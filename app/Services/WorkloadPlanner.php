<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TaskInstance;
use App\Models\WorkDay;
use Carbon\Carbon;

class WorkloadPlanner
{
    /**
     * Генерирует дневной план для сотрудника с учётом весов приоритетов.
     */
    public function generatePlan(Employee $employee, Carbon $from, Carbon $to): array
    {
        $workDays = WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->keyBy(fn ($d) => $d->date->toDateString());

        $assignments = TaskAssignment::with('task.subtasks')
            ->where('employee_id', $employee->id)
            ->whereHas('task', function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from);
            })
            ->get()
            ->sortBy('priority');

        // Оставшаяся работа в минутах
        $remaining = [];
        foreach ($assignments as $a) {
            $remaining[$a->id] = max(0, $a->task->total_avg_time * ($a->quota - $a->completed_count));
        }

        // Веса приоритетов: prio 1 → 50%, prio 2 → 30%, prio 3 → 15%, prio 4 → 5%, prio 5+ → 0%
        $priorityWeights = $this->calculatePriorityWeights($assignments);

        $plan = [];
        $current = $from->copy();

        while ($current->lte($to)) {
            $dateStr = $current->toDateString();
            $wd = $workDays->get($dateStr);
            $dayPlan = [];

            if ($wd && $wd->type === 'рабочий_день' && $wd->work_start) {
                $available = $wd->total_minutes;

                // Сначала раздаём время по приоритетам с учётом весов
                foreach ($priorityWeights as $priority => $weight) {
                    if ($available <= 0) {
                        break;
                    }

                    $tasksWithThisPriority = $assignments->where('priority', $priority);
                    if ($tasksWithThisPriority->isEmpty()) {
                        continue;
                    }

                    $timeForPriority = (int) round($available * $weight / 100);

                    foreach ($tasksWithThisPriority as $a) {
                        if (($remaining[$a->id] ?? 0) <= 0) {
                            continue;
                        }
                        if ($timeForPriority <= 0) {
                            break;
                        }

                        $alloc = min($remaining[$a->id], $timeForPriority);
                        if ($alloc <= 0) {
                            continue;
                        }

                        $dayPlan[$a->task_id] = ($dayPlan[$a->task_id] ?? 0) + $alloc;
                        $remaining[$a->id] -= $alloc;
                        $timeForPriority -= $alloc;
                        $available -= $alloc;

                        TaskInstance::updateOrCreate(
                            ['task_id' => $a->task_id, 'date' => $dateStr],
                            ['daily_quota' => (int) round($alloc / max(1, $a->task->total_avg_time))]
                        );
                    }
                }
            }

            $plan[$dateStr] = ['work_day' => $wd, 'tasks' => $dayPlan];
            $current->addDay();
        }

        return ['plan' => $plan, 'remaining' => $remaining, 'employee' => $employee];
    }

    /**
     * Веса приоритетов: чем выше приоритет, тем больше % времени.
     */
    private function calculatePriorityWeights($assignments): array
    {
        $groups = $assignments->groupBy('priority')->sortKeys();

        // Веса по умолчанию
        $defaultWeights = [1 => 50, 2 => 25, 3 => 15, 4 => 7, 5 => 3];

        $weights = [];
        $totalWeight = 0;

        foreach ($groups as $priority => $tasks) {
            $weight = $defaultWeights[$priority] ?? 1;
            $weights[$priority] = $weight;
            $totalWeight += $weight;
        }

        // Нормализуем до 100%
        if ($totalWeight > 0) {
            foreach ($weights as $priority => $weight) {
                $weights[$priority] = round(($weight / $totalWeight) * 100);
            }
        }

        return $weights;
    }
}
