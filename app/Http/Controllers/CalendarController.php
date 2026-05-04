<?php

namespace App\Http\Controllers;

use App\Models\EmployeePosition;
use App\Models\Task;
use App\Models\TaskFile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
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

        return view('admin.calendar.index', compact('employeePositions'));
    }

    // текущие события
    public function events(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $tasks = Task::where(function ($query) use ($start, $end) {
            $query->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                });
        })->get();

        $events = $tasks->map(function ($task) {
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
                    'commissariat_id' => $task->commissariat_id,
                    'employee_position_id' => $task->employee_position_id, // ✅ Добавьте эту строку
                ],
            ];
        });

        return response()->json($events);
    }

    public function show($id)
    {
        $task = Task::with(['employeePosition.employee.person', 'employeePosition.commissariatPosition'])->findOrFail($id);

        // Возвращаем отдельную страницу или компонент для просмотра задачи
        return view('admin.calendar.show', compact('task'));
    }

    public function store(Request $request)
    {
        \Log::info('Store method data:', $request->all());
        \Log::info('Files count: '.count($request->file('files', [])));

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'quota' => 'nullable|integer|min:1',
            'employee_position_id' => 'nullable|exists:employee_positions,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;

        $task = Task::create($validated);

        // Сохраняем файлы
        if ($request->hasFile('files')) {
            \Log::info('Saving files...');
            foreach ($request->file('files') as $file) {
                $path = $file->store('tasks/'.$task->id, 'public');

                \Log::info('File saved:', [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ]);

                TaskFile::create([
                    'task_id' => $task->id,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        } else {
            \Log::info('No files in request');
        }

        return response()->json([
            'success' => true,
            'event' => [
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
            ],
        ]);
    }

    public function update(Request $request, Task $task)
    {
        \Log::info('Update method called', ['id' => $task->id, 'data' => $request->all()]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'quota' => 'nullable|integer|min:1',
            'employee_position_id' => 'nullable|exists:employee_positions,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'files.*' => 'nullable|file|max:10240',   // разрешаем новые файлы
        ]);

        $task->update($validated);

        // Сохраняем новые файлы (существующие не трогаем)
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('tasks/'.$task->id, 'public');

                TaskFile::create([
                    'task_id' => $task->id,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Задача обновлена',
            'event' => [
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
            ],
        ]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['success' => true]);
    }

    // получаем файлы
    public function getFiles(Task $task)
    {
        $files = $task->files->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'size' => $file->size,
                'mime_type' => $file->mime_type,
                'url' => asset('storage/'.$file->path),
            ];
        });

        return response()->json($files);
    }
}
