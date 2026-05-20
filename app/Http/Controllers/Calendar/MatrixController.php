<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Task;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;

class MatrixController extends Controller
{
    public function index(Request $request, $commissariatId)
    {
        $commissariat = Commissariat::findOrFail($commissariatId);

        $tasks = Task::with(['employeePosition.employee.person'])
            ->whereHas('employeePosition.commissariatPosition', fn($q) => $q->where('commissariat_id', $commissariatId)
                ->orWhereHas('department', fn($dq) => $dq->where('commissariat_id', $commissariatId))
                ->orWhereHas('division', fn($dq) => $dq->where('commissariat_id', $commissariatId)))
            ->orderBy('start_date')
            ->get();

        $eps = \App\Models\EmployeePosition::with(['employee.person', 'commissariatPosition.position'])
            ->whereHas('commissariatPosition', fn($q) => $q->where('commissariat_id', $commissariatId)
                ->orWhereHas('department', fn($dq) => $dq->where('commissariat_id', $commissariatId))
                ->orWhereHas('division', fn($dq) => $dq->where('commissariat_id', $commissariatId)))
            ->whereIn('employee_position_status_id', [1, 2, 3])
            ->get();

        $employees = $eps->groupBy('employee_id')
            ->map(fn($group) => tap($group->first()->employee, fn($e) => $e->current_ep = $group->first()))
            ->values();

        $taskIds = $tasks->pluck('id')->toArray();
        $employeeIds = $employees->pluck('id')->toArray();

        $assignments = TaskAssignment::whereIn('task_id', $taskIds)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy('employee_id')
            ->map(fn($g) => $g->keyBy('task_id'));

        $matrix = $employees->map(fn($e) => [
            'employee' => $e,
            'tasks'    => $tasks->mapWithKeys(fn($t) => [$t->id => $assignments->get($e->id)?->get($t->id)]),
        ]);

        return view('admin.calendar.matrix.index', compact('commissariat', 'tasks', 'matrix'));
    }
}