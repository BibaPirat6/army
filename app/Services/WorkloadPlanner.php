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
     * Генерация плана загрузки сотрудника.
     */
    public function generatePlan(Employee $employee, Carbon $from, Carbon $to): array
    {
        $workDays = WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->keyBy(fn ($d) => $d->date->toDateString());

        $assignments = TaskAssignment::with('task')
            ->where('employee_id', $employee->id)
            ->whereHas('task', function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from);
            })
            ->get()
            ->sortBy('priority')
            ->values();

        /**
         * remaining[id] = сколько минут осталось выполнить КОНКРЕТНО ЭТОМУ СОТРУДНИКУ
         * 
         * Формула:
         * (quota - completed_count) * total_avg_time
         * 
         * quota - сколько всего итераций должен сделать сотрудник
         * completed_count - сколько уже сделал
         * total_avg_time - время на одну итерацию
         */
        $remaining = [];

        foreach ($assignments as $a) {
            $remainingQuota = max(0, $a->quota - $a->completed_count);
            $remaining[$a->id] = $a->task->total_avg_time * $remainingQuota;
            
            // Для отладки (можно убрать потом)
            \Log::info('Task calculation', [
                'task_id' => $a->task_id,
                'task_name' => $a->task->name,
                'quota_total' => $a->quota,
                'completed_count' => $a->completed_count,
                'remaining_quota' => $remainingQuota,
                'time_per_quota' => $a->task->total_avg_time,
                'remaining_minutes' => $remaining[$a->id],
            ]);
        }

        $plan = [];
        $current = $from->copy();

        while ($current->lte($to)) {

            $dateStr = $current->toDateString();
            $wd = $workDays->get($dateStr);

            $dayPlan = [];
            $taskMeta = [];
            $loadPercent = 0;

            if (
                $wd
                && $wd->type === 'рабочий_день'
                && $wd->work_start
            ) {

                $available = (int) ($wd->total_minutes ?? 0);

                $activeAssignments = $assignments->filter(function ($a) use ($remaining) {
                    return ($remaining[$a->id] ?? 0) > 0;
                });

                $requiredMinutes = 0;
                foreach ($activeAssignments as $a) {
                    $requiredMinutes += ($remaining[$a->id] ?? 0);
                }

                $loadPercent = $available > 0
                    ? (int) round($requiredMinutes / $available * 100)
                    : 0;

                if ($available > 0 && $activeAssignments->isNotEmpty()) {

                    $taskWeights = [];
                    foreach ($activeAssignments as $a) {
                        $priority = max(1, (int) $a->priority);
                        $taskWeights[$a->id] = 1 / (2 ** ($priority - 1));
                    }

                    $totalWeight = array_sum($taskWeights);

                    if ($totalWeight > 0) {

                        $usedMinutes = 0;
                        $sortedAssignments = $activeAssignments
                            ->sortBy('priority')
                            ->values();

                        foreach ($sortedAssignments as $index => $a) {

                            $weight = $taskWeights[$a->id] ?? 0;

                            if ($weight <= 0) {
                                continue;
                            }

                            $share = $weight / $totalWeight;
                            $alloc = (int) floor($available * $share);

                            $isLast = $index === ($sortedAssignments->count() - 1);

                            if ($isLast) {
                                $remainingDayMinutes = $available - $usedMinutes;
                                $alloc = max($alloc, $remainingDayMinutes);
                            }

                            $neededForTask = $remaining[$a->id];
                            $alloc = min($alloc, $neededForTask);

                            if ($alloc <= 0) {
                                continue;
                            }

                            if (!isset($dayPlan[$a->task_id])) {
                                $dayPlan[$a->task_id] = [
                                    'minutes' => 0,
                                    'assignment_id' => $a->id,
                                ];
                            }
                            $dayPlan[$a->task_id]['minutes'] += $alloc;

                            $remaining[$a->id] -= $alloc;
                            $usedMinutes += $alloc;

                            $isOverload = $remaining[$a->id] > 0;

                            // ПРАВИЛЬНОЕ название задачи из БД
                            $taskMeta[$a->task_id] = [
                                'minutes' => $dayPlan[$a->task_id]['minutes'],
                                'overload' => $isOverload,
                                'task_name' => $a->task->name, // Вот здесь берем реальное название!
                                'task_total_minutes' => $a->task->total_avg_time * $a->quota, // Общее время для сотрудника
                                'remaining_minutes' => $remaining[$a->id],
                                'quota_total' => $a->quota,
                                'completed_count' => $a->completed_count,
                                'remaining_quota' => max(0, $a->quota - $a->completed_count),
                            ];

                            TaskInstance::updateOrCreate(
                                [
                                    'task_id' => $a->task_id,
                                    'date' => $dateStr,
                                ],
                                [
                                    'daily_quota' => (int) round(
                                        $alloc / max(1, $a->task->total_avg_time)
                                    ),
                                ]
                            );
                        }

                        $leftover = $available - $usedMinutes;

                        if ($leftover > 0) {
                            $topTask = $sortedAssignments
                                ->sortBy('priority')
                                ->first();

                            if ($topTask) {
                                if (!isset($dayPlan[$topTask->task_id])) {
                                    $dayPlan[$topTask->task_id] = [
                                        'minutes' => 0,
                                        'assignment_id' => $topTask->id,
                                    ];
                                } elseif (!is_array($dayPlan[$topTask->task_id])) {
                                    $oldValue = $dayPlan[$topTask->task_id];
                                    $dayPlan[$topTask->task_id] = [
                                        'minutes' => $oldValue,
                                        'assignment_id' => $topTask->id,
                                    ];
                                }

                                $dayPlan[$topTask->task_id]['minutes'] += $leftover;

                                $taskMeta[$topTask->task_id] = [
                                    'minutes' => $dayPlan[$topTask->task_id]['minutes'],
                                    'overload' => true,
                                    'task_name' => $topTask->task->name,
                                    'task_total_minutes' => $topTask->task->total_avg_time * $topTask->quota,
                                    'remaining_minutes' => max(0, $remaining[$topTask->id] - $leftover),
                                ];

                                $remaining[$topTask->id] = max(
                                    0,
                                    ($remaining[$topTask->id] ?? 0) - $leftover
                                );
                            }
                        }
                    }
                }
            }

            $plan[$dateStr] = [
                'work_day' => $wd,
                'tasks' => $dayPlan,
                'task_meta' => $taskMeta,
                'load_percent' => $loadPercent,
            ];

            $current->addDay();
        }

        return [
            'plan' => $plan,
            'remaining' => $remaining,
            'employee' => $employee,
        ];
    }
}