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
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($assignment) {
            return redirect()->route('calendar.assignments.edit', [$task->id, $assignment->id]);
        }

        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id;

        // Доступная квота = квота задачи - сумма квот всех назначений
        $usedQuota = TaskAssignment::where('task_id', $task->id)->sum('quota');
        $availableQuota = max(0, ($task->quota ?? 0) - $usedQuota);

        return view('admin.calendar.assignments.form', compact('task', 'employee', 'assignment', 'commissariatId', 'availableQuota'));
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

        // Проверка квоты на сервере
        if ($task->quota) {
            $usedQuota = TaskAssignment::where('task_id', $task->id)->sum('quota');
            $availableQuota = $task->quota - $usedQuota;

            if ($validated['quota'] > $availableQuota) {
                return back()->withInput()->withErrors([
                    'quota' => "Доступно только {$availableQuota} из {$task->quota}. Другие сотрудники уже заняли {$usedQuota}."
                ]);
            }
        }

        // Проверка уникальности
        $exists = TaskAssignment::where('task_id', $task->id)
            ->where('employee_id', $validated['employee_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'employee_id' => 'Этот сотрудник уже назначен на задачу.'
            ]);
        }

        $validated['task_id'] = $task->id;
        TaskAssignment::create($validated);

        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;

        return redirect()->route('calendar.matrix.index', $commissariatId)
            ->with('success', 'Сотрудник назначен');
    }

    public function edit(Task $task, TaskAssignment $assignment)
    {
        $employee = $assignment->employee;
        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id;

        // Доступная квота = квота задачи - сумма квот всех назначений + текущая квота этого назначения
        $usedQuota = TaskAssignment::where('task_id', $task->id)
            ->where('id', '!=', $assignment->id)
            ->sum('quota');
        $availableQuota = max(0, ($task->quota ?? 0) - $usedQuota);

        return view('admin.calendar.assignments.form', compact('task', 'employee', 'assignment', 'commissariatId', 'availableQuota'));
    }

    public function update(Request $request, Task $task, TaskAssignment $assignment)
    {
        $validated = $request->validate([
            'quota'      => 'required|integer|min:1',
            'priority'   => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        // Проверка квоты на сервере
        if ($task->quota) {
            $usedQuota = TaskAssignment::where('task_id', $task->id)
                ->where('id', '!=', $assignment->id)
                ->sum('quota');
            $availableQuota = $task->quota - $usedQuota;

            if ($validated['quota'] > $availableQuota) {
                return back()->withInput()->withErrors([
                    'quota' => "Доступно только {$availableQuota} из {$task->quota}."
                ]);
            }
        }

        $assignment->update($validated);

        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;

        return redirect()->route('calendar.matrix.index', $commissariatId)
            ->with('success', 'Назначение обновлено');
    }

    public function destroy(Task $task, TaskAssignment $assignment)
    {
        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;
        $assignment->delete();

        return redirect()->route('calendar.matrix.index', $commissariatId)
            ->with('success', 'Назначение удалено');
    }
}