<?php

namespace App\Services;

use App\Models\Commissariat;
use App\Models\Task;

class TaskStatsService
{
    public function getStats(): array
    {
        $commissariats = Commissariat::with(['departments.divisions', 'divisions'])->get();
        $stats = [];

        foreach ($commissariats as $commissariat) {
            $stats[] = $this->buildCommissariatStats($commissariat);
        }

        return $stats;
    }

    private function buildCommissariatStats(Commissariat $commissariat): array
    {
        $directTasks = $this->countDirectTasks($commissariat->id, null, null);

        // Отделы
        $departments = [];
        foreach ($commissariat->departments as $department) {
            $departments[] = $this->buildDepartmentStats($commissariat->id, $department);
        }

        // Самостоятельные отделения (без отдела)
        $independentDivisions = [];
        foreach ($commissariat->divisions as $division) {
            if (is_null($division->department_id)) {
                $independentDivisions[] = $this->buildDivisionStats(
                    $commissariat->id,
                    null,
                    $division
                );
            }
        }

        return [
            'id'                    => $commissariat->id,
            'name'                  => $commissariat->name,
            'direct'                => $directTasks,
            'departments'           => $departments,
            'independent_divisions' => $independentDivisions,
            'total'                 => $directTasks
                + collect($departments)->sum('total')
                + collect($independentDivisions)->sum('tasks'),
        ];
    }

    private function buildDepartmentStats(int $commissariatId, $department): array
    {
        $directTasks = $this->countDirectTasks($commissariatId, $department->id, null);

        $divisions = [];
        foreach ($department->divisions as $division) {
            $divisions[] = $this->buildDivisionStats($commissariatId, $department->id, $division);
        }

        return [
            'id'        => $department->id,
            'name'      => $department->name,
            'direct'    => $directTasks,
            'divisions' => $divisions,
            'total'     => $directTasks + collect($divisions)->sum('tasks'),
        ];
    }

    private function buildDivisionStats(int $commissariatId, ?int $departmentId, $division): array
    {
        $tasks = $this->countDirectTasks($commissariatId, $departmentId, $division->id);

        return [
            'id'              => $division->id,
            'name'            => $division->name,
            'tasks'           => $tasks,
            'commissariat_id' => $commissariatId,
            'department_id'   => $departmentId,
        ];
    }

    private function countDirectTasks(?int $commissariatId, ?int $departmentId, ?int $divisionId): int
    {
        return Task::whereHas('employeePosition.commissariatPosition', function ($query) use ($commissariatId, $departmentId, $divisionId) {
            if ($commissariatId) {
                $query->where('commissariat_id', $commissariatId);
            }
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            } else {
                $query->whereNull('department_id');
            }
            if ($divisionId) {
                $query->where('division_id', $divisionId);
            } else {
                $query->whereNull('division_id');
            }
        })->count();
    }
}