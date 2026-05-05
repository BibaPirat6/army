<?php

namespace App\Services;

use App\Models\Commissariat;
use App\Models\Task;

class TaskStatsService
{
    public function getStats(): array
    {
        $commissariats = Commissariat::with(['departments.divisions'])->get();
        $stats = [];

        foreach ($commissariats as $commissariat) {
            $stats[] = $this->buildCommissariatStats($commissariat);
        }

        return $stats;
    }

    private function buildCommissariatStats(Commissariat $commissariat): array
    {
        $directTasks = $this->countDirectTasks($commissariat->id, null, null);

        $departments = [];
        foreach ($commissariat->departments as $department) {
            $departments[] = $this->buildDepartmentStats($department);
        }

        return [
            'id'          => $commissariat->id,
            'name'        => $commissariat->name,
            'direct'      => $directTasks,
            'departments' => $departments,
            'total'       => $directTasks + collect($departments)->sum('total'),
        ];
    }

    private function buildDepartmentStats($department): array
    {
        $directTasks = $this->countDirectTasks($department->commissariat_id, $department->id, null);

        $divisions = [];
        foreach ($department->divisions as $division) {
            $divisions[] = $this->buildDivisionStats($division);
        }

        return [
            'id'        => $department->id,
            'name'      => $department->name,
            'direct'    => $directTasks,
            'divisions' => $divisions,
            'total'     => $directTasks + collect($divisions)->sum('tasks'),
        ];
    }

    private function buildDivisionStats($division): array
    {
        $tasks = $this->countDirectTasks($division->commissariat_id, $division->department_id, $division->id);

        return [
            'id'    => $division->id,
            'name'  => $division->name,
            'tasks' => $tasks,
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