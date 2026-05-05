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
    public function index(
        Request $request,
        $commissariatId,
        $departmentId = null,
        $divisionId = null
    ) {
        $commissariat = Commissariat::findOrFail($commissariatId);

        $department = null;
        $division = null;
        $unitName = $commissariat->name;
        $unitType = 'commissariat';

        // Определяем, что передано
        $routeParams = $request->route()->parameters();

        if (isset($routeParams['division'])) {
            // Отделение (может быть самостоятельным)
            $division = Division::findOrFail($divisionId);
            $unitName = $division->name;
            $unitType = 'division';

            if ($division->department_id) {
                $department = Department::find($division->department_id);
            }
        } elseif (isset($routeParams['department'])) {
            // Отдел
            $department = Department::findOrFail($departmentId);
            $unitName = $department->name;
            $unitType = 'department';
        }

        // Получаем задачи
        $tasks = $this->getTasks($commissariatId, $department?->id, $division?->id);

        // Получаем сотрудников
        $employees = $this->getEmployees($commissariatId, $department?->id, $division?->id);

        // Получаем назначения
        $taskIds = $tasks->pluck('id')->toArray();
        $assignments = TaskAssignment::whereIn('task_id', $taskIds)
            ->with(['employee.person'])
            ->get()
            ->groupBy('task_id');

        // Строим матрицу
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

        // Хлебные крошки
        $breadcrumbs = [
            ['name' => 'Календарь', 'url' => route('calendar.index')],
            ['name' => 'Матрица', 'url' => null],
            ['name' => $commissariat->name, 'url' => null],
        ];

        if ($department) {
            $breadcrumbs[] = ['name' => $department->name, 'url' => null];
        }
        if ($division) {
            $breadcrumbs[] = ['name' => $division->name, 'url' => null];
        }

        return view('admin.calendar.matrix.index', compact(
            'commissariat',
            'department',
            'division',
            'unitName',
            'unitType',
            'breadcrumbs',
            'employees',
            'tasks',
            'matrix',
            'totals'
        ));
    }

    /**
     * Получить задачи с учётом иерархии.
     */
    private function getTasks($commissariatId, $departmentId, $divisionId)
    {
        $query = Task::with(['subtasks'])
            ->whereHas('employeePosition.commissariatPosition', function ($q) use ($commissariatId) {
                $q->where('commissariat_id', $commissariatId);
            });

        if ($divisionId) {
            // Конкретное отделение
            $query->whereHas('employeePosition.commissariatPosition', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        } elseif ($departmentId) {
            // Отдел: задачи напрямую отдела + всех его отделений
            $divisionIds = Division::where('department_id', $departmentId)->pluck('id')->toArray();
            $query->whereHas('employeePosition.commissariatPosition', function ($q) use ($departmentId, $divisionIds) {
                $q->where(function ($sub) use ($departmentId, $divisionIds) {
                    $sub->where('department_id', $departmentId);
                    if (!empty($divisionIds)) {
                        $sub->orWhereIn('division_id', $divisionIds);
                    }
                });
            });
        }
        // Комиссариат: все задачи (напрямую + отделы + отделения)

        return $query->orderBy('start_date')->get();
    }

    /**
     * Получить сотрудников с учётом иерархии.
     */
    private function getEmployees($commissariatId, $departmentId, $divisionId)
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
                    $sub->where('department_id', $departmentId);
                    if (!empty($divisionIds)) {
                        $sub->orWhereIn('division_id', $divisionIds);
                    }
                });
            });
        }

        return $query->get();
    }
}