<?php

namespace App\Services;

use App\Models\Commissariat;
use App\Models\Task;

class TaskStatsService
{
    public function getStats(): array
    {
        return Commissariat::all()
            ->map(fn($c) => [
                'id'    => $c->id,
                'name'  => $c->name,
                'total' => Task::whereHas('employeePosition.commissariatPosition', 
                    fn($q) => $q->where('commissariat_id', $c->id)
                )->count(),
            ])
            ->toArray();
    }
}