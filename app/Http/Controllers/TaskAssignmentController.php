<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;

class TaskAssignmentController extends Controller
{
    public function create(Task $task, Employee $employee)
    {
        // Проверяем, существует ли уже назначение
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->first();

        return view('admin.calendar.assignments.create', compact('task', 'employee', 'assignment'));
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'quota'       => 'required|integer|min:1',
            'priority'    => 'required|integer|min:1',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['task_id'] = $task->id;

        TaskAssignment::create($validated);

        return redirect()->back()->with('success', 'Сотрудник назначен');
    }

    public function edit(Task $task, TaskAssignment $assignment)
    {
        $employee = $assignment->employee;
        return view('admin.calendar.assignments.form', compact('task', 'employee', 'assignment'));
    }

    public function update(Request $request, Task $task, TaskAssignment $assignment)
    {
        $validated = $request->validate([
            'quota'      => 'required|integer|min:1',
            'priority'   => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $assignment->update($validated);

        return redirect()->back()->with('success', 'Назначение обновлено');
    }

    public function destroy(Task $task, TaskAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Назначение удалено');
    }
}