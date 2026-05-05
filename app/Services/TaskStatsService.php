<?php

namespace App\Services;

use App\Models\Commissariat;
use App\Models\Task;

class TaskStatsService
{
    public function getStats(): array
    {
        $commissariats = Commissariat::all();
        $stats = [];

        foreach ($commissariats as $commissariat) {
            $totalTasks = Task::whereHas('employeePosition.commissariatPosition', function ($q) use ($commissariat) {
                $q->where('commissariat_id', $commissariat->id);
            })->count();

            $stats[] = [
                'id'    => $commissariat->id,
                'name'  => $commissariat->name,
                'total' => $totalTasks,
            ];
        }

        return $stats;
    }
}