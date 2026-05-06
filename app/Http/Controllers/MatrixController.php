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

        // Задачи с ответственным
        $tasks = Task::with(['subtasks', 'employeePosition.employee.person'])
            ->whereHas('employeePosition.commissariatPosition', fn($q) => $q->where('commissariat_id', $commissariatId))
            ->orderBy('start_date')
            ->get();

        // АБСОЛЮТНО ВСЕ сотрудники через commissariat_positions → employee_positions
        $employeeIds = \DB::table('employee_positions')
            ->join('commissariat_positions', 'employee_positions.commissariat_position_id', '=', 'commissariat_positions.id')
            ->where('commissariat_positions.commissariat_id', $commissariatId)
            ->pluck('employee_positions.employee_id')
            ->unique()
            ->filter() // убираем null
            ->values();

        $employees = Employee::with(['person', 'employeePositions.commissariatPosition.position'])
            ->whereIn('id', $employeeIds)
            ->get();

        // Назначения
        $taskIds = $tasks->pluck('id')->toArray();
        $assignments = TaskAssignment::whereIn('task_id', $taskIds)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy('employee_id')
            ->map(fn($group) => $group->keyBy('task_id'));

        // Матрица
        $matrix = [];
        foreach ($employees as $employee) {
            $empAssignments = $assignments->get($employee->id, collect());
            $row = ['employee' => $employee, 'tasks' => []];
            foreach ($tasks as $task) {
                $row['tasks'][$task->id] = $empAssignments->get($task->id);
            }
            $matrix[] = $row;
        }

        return view('admin.calendar.matrix.index', compact('commissariat', 'tasks', 'employees', 'matrix'));
    }
}