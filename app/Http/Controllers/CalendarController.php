<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Task;
use App\Models\TaskFile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::all();

        return view('admin.calendar.index', compact('commissariats'));
    }

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
                ],
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'quota' => 'nullable|integer|min:1',
            'commissariat_id' => 'nullable|exists:commissariats,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $validated['created_by'] = auth()->id() ?? 1;

        $task = Task::create($validated);

        // Сохраняем файлы
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
            'message' => 'Задача создана',
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
                    'commissariat_id' => $task->commissariat_id,
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
            'commissariat_id' => 'nullable|exists:commissariats,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $task->update($validated);

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
                    'commissariat_id' => $task->commissariat_id,
                ],
            ],
        ]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['success' => true]);
    }

    public function getFiles(Task $task)
    {
        $files = $task->files->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'size' => $file->size,
                'url' => asset('storage/'.$file->path),
            ];
        });

        return response()->json($files);
    }
}
