<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Employee;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;

class TaskAssignmentController extends Controller
{
    public function create(Task $task, Employee $employee)
    {
        // Проверяем, есть ли уже назначение
        $existingAssignment = TaskAssignment::where('task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existingAssignment) {
            return redirect()->route('calendar.assignments.edit', [$task->id, $existingAssignment->id]);
        }

        // Расчет доступной квоты
        $usedQuota = TaskAssignment::where('task_id', $task->id)->sum('quota');
        $availableQuota = max(0, ($task->quota ?? 0) - $usedQuota);
        
        // Получаем ID комиссариата для возврата
        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;
        
        return view('admin.calendar.assignments.create', compact('task', 'employee', 'availableQuota', 'commissariatId'));
    }
    
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'quota' => 'required|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        // Проверка доступной квоты
        $usedQuota = TaskAssignment::where('task_id', $task->id)->sum('quota');
        $availableQuota = ($task->quota ?? 0) - $usedQuota;
        
        if ($validated['quota'] > $availableQuota) {
            return back()->withInput()->withErrors([
                'quota' => "Доступно только {$availableQuota} из {$task->quota}. Другие сотрудники уже заняли {$usedQuota}."
            ]);
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
        
        $assignment = TaskAssignment::create([
            'task_id' => $task->id,
            'employee_id' => $validated['employee_id'],
            'quota' => $validated['quota'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? $task->start_date,
            'end_date' => $validated['end_date'] ?? $task->end_date,
            'completed_count' => 0,
        ]);
        
        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;
        
        return redirect()
            ->route('calendar.matrix.index', $commissariatId)
            ->with('success', 'Сотрудник назначен на задачу');
    }
    
    public function edit(Task $task, TaskAssignment $assignment)
    {
        $employee = $assignment->employee;
        
        // Расчет доступной квоты (исключая текущее назначение)
        $usedQuota = TaskAssignment::where('task_id', $task->id)
            ->where('id', '!=', $assignment->id)
            ->sum('quota');
        $availableQuota = max(0, ($task->quota ?? 0) - $usedQuota);
        
        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;
        
        return view('admin.calendar.assignments.edit', compact('task', 'assignment', 'employee', 'availableQuota', 'commissariatId'));
    }
    
    public function update(Request $request, Task $task, TaskAssignment $assignment)
    {
        $validated = $request->validate([
            'quota' => 'required|integer|min:1',
            'priority' => 'required|integer|min:1|max:10',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        // Проверка доступной квоты (исключая текущее назначение)
        $usedQuota = TaskAssignment::where('task_id', $task->id)
            ->where('id', '!=', $assignment->id)
            ->sum('quota');
        $availableQuota = ($task->quota ?? 0) - $usedQuota;
        
        if ($validated['quota'] > $availableQuota + $assignment->quota) {
            return back()->withInput()->withErrors([
                'quota' => "Доступно только {$availableQuota} из {$task->quota}."
            ]);
        }
        
        $assignment->update([
            'quota' => $validated['quota'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? $task->start_date,
            'end_date' => $validated['end_date'] ?? $task->end_date,
        ]);
        
        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;
        
        return redirect()
            ->route('calendar.matrix.index', $commissariatId)
            ->with('success', 'Назначение обновлено');
    }
    
    public function destroy(Task $task, TaskAssignment $assignment)
    {
        $assignment->delete();
        
        $commissariatId = $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1;
        
        return redirect()
            ->route('calendar.matrix.index', $commissariatId)
            ->with('success', 'Назначение удалено');
    }
}