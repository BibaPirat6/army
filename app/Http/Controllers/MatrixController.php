<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Employee;
use Illuminate\Http\Request;

class MatrixController extends Controller
{
    public function index(Request $request, $commissariatId, $departmentId = null, $divisionId = null)
    {
        $commissariat = Commissariat::findOrFail($commissariatId);

        $department = null;
        $division = null;
        $unitName = $commissariat->name;
        $unitType = 'commissariat';
        $breadcrumbs = [
            ['name' => 'Календарь', 'url' => route('calendar.index')],
            ['name' => 'Матрица', 'url' => null],
            ['name' => $unitName, 'url' => null],
        ];

        if ($divisionId) {
            $division = Division::findOrFail($divisionId);
            $unitName = $division->name;
            $unitType = 'division';
            if ($division->department_id) {
                $department = $division->department;
                $breadcrumbs[] = ['name' => $department->name, 'url' => null];
            }
            $breadcrumbs[] = ['name' => $unitName, 'url' => null];
        } elseif ($departmentId) {
            $department = Department::findOrFail($departmentId);
            $unitName = $department->name;
            $unitType = 'department';
            $breadcrumbs[] = ['name' => $unitName, 'url' => null];
        }

        // Получаем ВСЕ задачи (с учётом иерархии)
        $tasks = $this->getAllTasks($commissariatId, $departmentId, $divisionId);

        // Получаем ВСЕХ сотрудников подразделения (включая вложенные)
        $allEmployees = $this->getAllEmployees($commissariatId, $departmentId, $divisionId);

        // Собираем все назначения для этих задач
        $taskIds = $tasks->pluck('id')->toArray();
        $assignments = TaskAssignment::whereIn('task_id', $taskIds)
            ->with(['employee.person', 'task'])
            ->get()
            ->groupBy('task_id');

        // Строим матрицу: задача → сотрудники
        $matrix = [];
        $totals = [
            'tasks'       => count($tasks),
            'employees'   => count($allEmployees),
            'assignments' => $assignments->flatten(1)->count(),
            'unassigned'  => 0,
        ];

        foreach ($tasks as $task) {
            $taskAssignments = $assignments->get($task->id, collect());

            $row = [
                'task'        => $task,
                'assignments' => $taskAssignments,
                'total_quota' => $taskAssignments->sum('quota'),
                'total_completed' => $taskAssignments->sum('completed_count'),
            ];

            if ($taskAssignments->isEmpty()) {
                $totals['unassigned']++;
            }

            $matrix[] = $row;
        }

        return view('admin.calendar.matrix.index', compact(
            'commissariat',
            'department',
            'division',
            'unitName',
            'unitType',
            'breadcrumbs',
            'allEmployees',
            'tasks',
            'matrix',
            'totals'
        ));
    }

    /**
     * Получить ВСЕ задачи с учётом иерархии.
     */
    private function getAllTasks($commissariatId, $departmentId, $divisionId)
    {
        $query = Task::with(['subtasks', 'employeePosition.commissariatPosition.commissariat'])
            ->whereHas('employeePosition.commissariatPosition', function ($q) use ($commissariatId) {
                $q->where('commissariat_id', $commissariatId);
            });

        if ($divisionId) {
            // Только задачи конкретного отделения
            $query->whereHas('employeePosition.commissariatPosition', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        } elseif ($departmentId) {
            // Задачи напрямую отдела + задачи всех его отделений
            $divisionIds = Division::where('department_id', $departmentId)->pluck('id')->toArray();
            $query->whereHas('employeePosition.commissariatPosition', function ($q) use ($departmentId, $divisionIds) {
                $q->where(function ($sub) use ($departmentId, $divisionIds) {
                    $sub->where('department_id', $departmentId)
                        ->orWhereIn('division_id', $divisionIds);
                });
            });
        }
        // Если только комиссариат — задачи напрямую комиссариата + всех отделов + всех отделений

        return $query->orderBy('start_date')->get();
    }

    /**
     * Получить ВСЕХ сотрудников с учётом иерархии.
     */
    private function getAllEmployees($commissariatId, $departmentId, $divisionId)
    {
        $query = Employee::with(['person'])
            ->whereHas('employeePositions.commissariatPosition', function ($q) use ($commissariatId) {
                $q->where('commissariat_id', $commissariatId);
            });

        if ($divisionId) {
            $query->whereHas('employeePositions.commissariatPosition', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        } elseif ($departmentId) {
            $divisionIds = Division::where('department_id', $departmentId)->pluck('id')->toArray();
            $query->whereHas('employeePositions.commissariatPosition', function ($q) use ($departmentId, $divisionIds) {
                $q->where(function ($sub) use ($departmentId, $divisionIds) {
                    $sub->where('department_id', $departmentId)
                        ->orWhereIn('division_id', $divisionIds);
                });
            });
        }

        return $query->get();
    }
}