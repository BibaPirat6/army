<?php

// app/Http/Controllers/SubtaskController.php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function index(Task $task)
    {
        return response()->json([
            'success' => true,
            'subtasks' => $task->subtasks,
        ]);
    }

    public function show(Task $task, Subtask $subtask)
    {
        return response()->json([
            'success' => true,
            'subtask' => $subtask,
        ]);
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'min_time_minutes' => 'required|integer|min:0',
            'avg_time_minutes' => 'required|integer|min:0',
            'max_time_minutes' => 'required|integer|min:0',
        ]);

        // Дополнительная валидация: min <= avg <= max
        if ($validated['min_time_minutes'] > $validated['avg_time_minutes']) {
            return response()->json([
                'success' => false,
                'message' => 'Минимальное время не может быть больше среднего',
                'field' => 'min_time_minutes',
            ], 422);
        }

        if ($validated['avg_time_minutes'] > $validated['max_time_minutes']) {
            return response()->json([
                'success' => false,
                'message' => 'Среднее время не может быть больше максимального',
                'field' => 'avg_time_minutes',
            ], 422);
        }

        if ($validated['min_time_minutes'] > $validated['max_time_minutes']) {
            return response()->json([
                'success' => false,
                'message' => 'Минимальное время не может быть больше максимального',
                'field' => 'min_time_minutes',
            ], 422);
        }

        $subtask = $task->subtasks()->create($validated);

        return response()->json([
            'success' => true,
            'subtask' => $subtask,
            'message' => 'Подзадача создана',
        ]);
    }

    public function update(Request $request, Task $task, Subtask $subtask)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'min_time_minutes' => 'required|integer|min:0',
            'avg_time_minutes' => 'required|integer|min:0',
            'max_time_minutes' => 'required|integer|min:0',
        ]);

        // Дополнительная валидация: min <= avg <= max
        if ($validated['min_time_minutes'] > $validated['avg_time_minutes']) {
            return response()->json([
                'success' => false,
                'message' => 'Минимальное время не может быть больше среднего',
                'field' => 'min_time_minutes',
            ], 422);
        }

        if ($validated['avg_time_minutes'] > $validated['max_time_minutes']) {
            return response()->json([
                'success' => false,
                'message' => 'Среднее время не может быть больше максимального',
                'field' => 'avg_time_minutes',
            ], 422);
        }

        if ($validated['min_time_minutes'] > $validated['max_time_minutes']) {
            return response()->json([
                'success' => false,
                'message' => 'Минимальное время не может быть больше максимального',
                'field' => 'min_time_minutes',
            ], 422);
        }

        $subtask->update($validated);

        return response()->json([
            'success' => true,
            'subtask' => $subtask,
            'message' => 'Подзадача обновлена',
        ]);
    }

    public function destroy(Task $task, Subtask $subtask)
    {
        $subtask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Подзадача удалена',
        ]);
    }
}
