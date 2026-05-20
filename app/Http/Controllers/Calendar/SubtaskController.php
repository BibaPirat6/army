<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function index(Task $task)
    {
        $subtasks = $task->subtasks()->orderBy('id')->get();
        return view('admin.calendar.tasks.subtasks.index', compact('task', 'subtasks'));
    }

    public function create(Task $task)
    {
        return view('admin.calendar.tasks.subtasks.create', compact('task'));
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'min_time_minutes' => 'required|integer|min:0',
            'avg_time_minutes' => 'required|integer|min:0',
            'max_time_minutes' => 'required|integer|min:0',
        ]);

        if ($validated['min_time_minutes'] > $validated['avg_time_minutes']) {
            return back()->withErrors(['min_time_minutes' => 'Минимальное время не может быть больше среднего'])->withInput();
        }

        if ($validated['avg_time_minutes'] > $validated['max_time_minutes']) {
            return back()->withErrors(['avg_time_minutes' => 'Среднее время не может быть больше максимального'])->withInput();
        }

        $task->subtasks()->create($validated);

        return redirect()
            ->route('calendar.tasks.subtasks.index', $task)
            ->with('success', 'Подзадача успешно создана');
    }

    public function show(Task $task, Subtask $subtask)
    {
        if ($subtask->task_id !== $task->id) {
            abort(404);
        }
        return view('admin.calendar.tasks.subtasks.show', compact('task', 'subtask'));
    }

    public function edit(Task $task, Subtask $subtask)
    {
        if ($subtask->task_id !== $task->id) {
            abort(404);
        }
        return view('admin.calendar.tasks.subtasks.edit', compact('task', 'subtask'));
    }

    public function update(Request $request, Task $task, Subtask $subtask)
    {
        if ($subtask->task_id !== $task->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'min_time_minutes' => 'required|integer|min:0',
            'avg_time_minutes' => 'required|integer|min:0',
            'max_time_minutes' => 'required|integer|min:0',
        ]);

        if ($validated['min_time_minutes'] > $validated['avg_time_minutes']) {
            return back()->withErrors(['min_time_minutes' => 'Минимальное время не может быть больше среднего'])->withInput();
        }

        if ($validated['avg_time_minutes'] > $validated['max_time_minutes']) {
            return back()->withErrors(['avg_time_minutes' => 'Среднее время не может быть больше максимального'])->withInput();
        }

        $subtask->update($validated);

        return redirect()
            ->route('calendar.tasks.subtasks.index', $task)
            ->with('success', 'Подзадача успешно обновлена');
    }

    public function destroy(Task $task, Subtask $subtask)
    {
        if ($subtask->task_id !== $task->id) {
            abort(404);
        }

        $subtask->delete();

        return redirect()
            ->route('calendar.tasks.subtasks.index', $task)
            ->with('success', 'Подзадача успешно удалена');
    }
}