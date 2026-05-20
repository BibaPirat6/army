<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\EmployeePosition;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function create(Request $request)
    {
        $employeePositions = EmployeePosition::with([
            'employee.person',
            'commissariatPosition.position',
            'commissariatPosition.commissariat',
            'commissariatPosition.department',
            'commissariatPosition.division',
        ])
            ->whereHas('commissariatPosition.position.chiefType', function ($query) {
                $query->whereIn('id', [2, 3, 4]);
            })
            ->get()
            ->map(function ($ep) {
                $person = $ep->employee?->person;
                $cp = $ep->commissariatPosition;

                // ФИО
                $fullName = $person
                    ? trim($person->фамилия.' '.$person->имя.' '.($person->отчество ?? ''))
                    : 'Сотрудник #'.$ep->employee_id;

                // Должность
                $positionName = $cp->position?->name ?? '';

                // Подразделение
                $unitName = $cp->division?->name
                    ?? $cp->department?->name
                    ?? $cp->commissariat?->name
                    ?? '';

                return [
                    'id' => $ep->id,
                    'full_name' => $fullName,
                    'position' => $positionName,
                    'unit' => $unitName,
                    'search_text' => mb_strtolower($fullName.' '.$positionName.' '.$unitName),
                ];
            });

        $startDate = $request->get('start_date', now()->format('Y-m-d'));

        return view('admin.calendar.tasks.create', compact('employeePositions', 'startDate'));
    }

    public function show($id)
    {
        $task = Task::with([
            'employeePosition.employee.person',
            'employeePosition.commissariatPosition',
            'subtasks',
            'taskAssignments.employee.person',
        ])->findOrFail($id);

        $subtasks = $task->subtasks;
        $totalMin = $subtasks->sum('min_time_minutes');
        $totalAvg = $subtasks->sum('avg_time_minutes');
        $totalMax = $subtasks->sum('max_time_minutes');
        $totalCompleted = $task->taskAssignments->sum('completed_count');
        $totalQuotaAssigned = $task->taskAssignments->sum('quota');

        return view('admin.calendar.tasks.show', compact(
            'task', 'subtasks', 'totalMin', 'totalAvg', 'totalMax', 'totalCompleted', 'totalQuotaAssigned'
        ));
    }

    public function edit(Task $task)
    {
        $employeePositions = EmployeePosition::with([
            'employee.person',
            'commissariatPosition.position',
            'commissariatPosition.commissariat',
            'commissariatPosition.department',
            'commissariatPosition.division',
        ])
            ->whereHas('commissariatPosition.position.chiefType', function ($query) {
                $query->whereIn('id', [2, 3, 4]);
            })
            ->get()
            ->map(function ($ep) {
                $person = $ep->employee?->person;
                $cp = $ep->commissariatPosition;

                // ФИО
                $fullName = $person
                    ? trim($person->фамилия.' '.$person->имя.' '.($person->отчество ?? ''))
                    : 'Сотрудник #'.$ep->employee_id;

                // Должность
                $positionName = $cp->position?->name ?? '';

                // Подразделение
                $unitName = $cp->division?->name
                    ?? $cp->department?->name
                    ?? $cp->commissariat?->name
                    ?? '';

                return [
                    'id' => $ep->id,
                    'full_name' => $fullName,
                    'position' => $positionName,
                    'unit' => $unitName,
                    'search_text' => mb_strtolower($fullName.' '.$positionName.' '.$unitName),
                ];
            });

        // Находим выбранного ответственного для отображения
        $selectedResponsible = null;
        if ($task->employee_position_id) {
            $selected = collect($employeePositions)->firstWhere('id', $task->employee_position_id);
            if ($selected) {
                $selectedResponsible = $selected;
            }
        }

        return view('admin.calendar.tasks.edit', compact('task', 'employeePositions', 'selectedResponsible'));
    }

 public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        'quota' => 'nullable|integer|min:1',
        'employee_position_id' => 'nullable|exists:employee_positions,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'files.*' => 'nullable|file|max:10240', // 10 МБ
    ]);

    $validated['created_by'] = auth()->id() ?? 1;
    $validated['files'] = [];

    $task = Task::create($validated);

    // Сохранение файлов (только валидные)
    if ($request->hasFile('files')) {
        $files = $request->file('files');
        
        foreach ($files as $file) {
            // Дополнительная проверка размера
            if ($file->isValid() && $file->getSize() <= 10 * 1024 * 1024) {
                $task->addFile($file);
            }
        }
    }

    return redirect()
        ->route('calendar.tasks.show', $task)
        ->with('success', 'Задача успешно создана');
}

 public function update(Request $request, Task $task)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        'quota' => 'nullable|integer|min:1',
        'employee_position_id' => 'nullable|exists:employee_positions,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'files.*' => 'nullable|file|max:10240', // 10 МБ
    ]);

    $task->update($validated);

    // Сохранение новых файлов (только если они есть и валидны)
    if ($request->hasFile('files')) {
        $files = $request->file('files');
        // Фильтруем только валидные файлы
        $validFiles = array_filter($files, function($file) {
            return $file->isValid() && $file->getSize() <= 10240 * 1024; // 10 МБ
        });
        
        foreach ($validFiles as $file) {
            $task->addFile($file);
        }
    }

    return redirect()
        ->route('calendar.tasks.show', $task)
        ->with('success', 'Задача успешно обновлена');
}

    /**
     * Удаление задачи
     */
    public function destroy(Task $task)
    {
        try {
            // Удаляем все файлы задачи
            foreach ($task->getFilesList() as $file) {
                if (isset($file['path']) && Storage::disk('public')->exists($file['path'])) {
                    Storage::disk('public')->delete($file['path']);
                }
            }

            // Удаляем подзадачи
            $task->subtasks()->delete();

            // Удаляем назначения
            $task->taskAssignments()->delete();

            // Удаляем задачу
            $task->delete();

            return redirect()
                ->route('calendar.index')
                ->with('success', 'Задача успешно удалена');
        } catch (\Exception $e) {
            \Log::error('Ошибка удаления задачи: '.$e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Ошибка при удалении задачи');
        }
    }

    /**
     * Удаление файла задачи
     */
    public function deleteFile(Task $task, $fileId)
    {
        try {
            $result = $task->removeFile($fileId);

            if ($result) {
                return redirect()->back()->with('success', 'Файл успешно удален');
            } else {
                return redirect()->back()->with('error', 'Файл не найден');
            }
        } catch (\Exception $e) {
            \Log::error('Ошибка удаления файла: '.$e->getMessage());

            return redirect()->back()->with('error', 'Ошибка при удалении файла');
        }
    }
}
