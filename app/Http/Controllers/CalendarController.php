<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Task;
use Carbon\Carbon;
use Request;

class CalendarController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::all();

        return view('admin.calendar.index', compact('commissariats'));
    }

    public function store(Request $request)
    {
        dd(1);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'quota' => 'nullable|integer|min:1',
            'commissariat_id' => 'nullable|exists:commissariats,id',
            'department_id' => 'nullable|exists:departments,id',
            'division_id' => 'nullable|exists:divisions,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['created_by'] = auth()->id() ?? 1; // затычка, если нет авторизации

        $task = Task::create($validated);

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
}
