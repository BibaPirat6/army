<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\EmployeePosition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Storage;

class TaskController extends Controller
{
    public function create(Request $request)
    {
        $employeePositions = EmployeePosition::with([
            'employee.person',
            'commissariatPosition.position.chiefType',
            'commissariatPosition.commissariat',
            'commissariatPosition.department',
            'commissariatPosition.division',
        ])
            ->whereHas('commissariatPosition.position.chiefType', function ($query) {
                $query->whereIn('id', [2, 3, 4]);
            })
            ->get();

        $startDate = $request->get('start_date', now()->format('Y-m-d'));
        
        return view('admin.calendar.tasks.create', compact('employeePositions', 'startDate'));
    }

    public function show($id)
    {
        $task = Task::with([
            'employeePosition.employee.person',
            'employeePosition.commissariatPosition',
        ])->findOrFail($id);

        return view('admin.calendar.tasks.show', compact('task'));
    }

    public function store(Request $request)
    {
        \Log::info('Store method data:', $request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'quota' => 'nullable|integer|min:1',
            'employee_position_id' => 'nullable|exists:employee_positions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;
        $validated['files'] = []; // Пустой массив для файлов

        $task = Task::create($validated);

        // Сохраняем файлы в JSON поле
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $task->addFile($file);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Задача создана',
                'task_id' => $task->id,
                'event' => $this->formatEvent($task),
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Задача создана');
    }

    public function update(Request $request, Task $task)
    {
        \Log::info('Update method called', ['id' => $task->id]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'quota' => 'nullable|integer|min:1',
            'employee_position_id' => 'nullable|exists:employee_positions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $task->update($validated);

        // Добавляем новые файлы
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $task->addFile($file);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Задача обновлена',
                'event' => $this->formatEvent($task),
            ]);
        }

        return redirect()->route('calendar.tasks.show', $task)->with('success', 'Задача обновлена');
    }

    public function destroy(Task $task)
    {
        // Удаляем физические файлы
        $files = $task->getFiles();
        foreach ($files as $file) {
            if (Storage::disk('public')->exists($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }
        }
        
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Задача удалена',
        ]);
    }

    /**
     * Удалить конкретный файл из задачи
     */
    public function deleteFile(Task $task, $fileId)
    {
        $task->removeFile($fileId);
        
        return response()->json([
            'success' => true,
            'message' => 'Файл удален',
        ]);
    }

    /**
     * Получить файлы задачи
     */
    public function getFiles(Task $task)
    {
        return response()->json($task->getFilesWithUrls());
    }

    private function formatEvent(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'start' => $task->start_date->toDateString(),
            'end' => $task->end_date
                ? Carbon::parse($task->end_date)->addDay()->toDateString()
                : Carbon::parse($task->start_date)->addDay()->toDateString(),
            'color' => $task->color,
            'extendedProps' => [
                'description' => $task->description,
                'quota' => $task->quota,
                'employee_position_id' => $task->employee_position_id,
            ],
        ];
    }
}