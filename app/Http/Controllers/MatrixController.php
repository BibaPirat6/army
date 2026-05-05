<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;

class MatrixController extends Controller
{
    public function index(Request $request, $commissariatId)
    {
        $commissariat = Commissariat::findOrFail($commissariatId);

        // Все задачи комиссариата
        $tasks = Task::with(['subtasks'])
            ->whereHas('employeePosition.commissariatPosition', function ($q) use ($commissariatId) {
                $q->where('commissariat_id', $commissariatId);
            })
            ->orderBy('start_date')
            ->get();

        // Все сотрудники комиссариата (включая начальников)
        $employees = Employee::with(['person'])
            ->whereHas('employeePositions', function ($epQuery) use ($commissariatId) {
                // Убрал фильтр по статусу — берем всех
                $epQuery->whereHas('commissariatPosition', function ($cpQuery) use ($commissariatId) {
                    $cpQuery->where('commissariat_id', $commissariatId);
                });
            })
            ->get();

        // Назначения
        $taskIds = $tasks->pluck('id')->toArray();
        $assignments = TaskAssignment::whereIn('task_id', $taskIds)
            ->with(['employee.person'])
            ->get()
            ->groupBy('task_id');

        // Матрица
        $matrix = [];
        $totals = [
            'tasks'      => count($tasks),
            'employees'  => count($employees),
            'assignments' => $assignments->flatten(1)->count(),
            'unassigned' => 0,
        ];

        foreach ($tasks as $task) {
            $taskAssignments = $assignments->get($task->id, collect());

            $row = [
                'task'            => $task,
                'assignments'     => $taskAssignments,
                'total_quota'     => $taskAssignments->sum('quota'),
                'total_completed' => $taskAssignments->sum('completed_count'),
            ];

            if ($taskAssignments->isEmpty()) {
                $totals['unassigned']++;
            }

            $matrix[] = $row;
        }

        $breadcrumbs = [
            ['name' => 'Календарь', 'url' => route('calendar.index')],
            ['name' => $commissariat->name, 'url' => null],
        ];

        return view('admin.calendar.matrix.index', compact(
            'commissariat',
            'breadcrumbs',
            'employees',
            'tasks',
            'matrix',
            'totals'
        ));
    }
}