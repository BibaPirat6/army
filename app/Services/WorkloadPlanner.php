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

        $assignments = TaskAssignment::with('task.subtasks')
            ->where('employee_id', $employee->id)
            ->whereHas('task', function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from);
            })
            ->get()
            ->sortBy('priority')
            ->values();

        /**
         * remaining[id] = сколько минут осталось
         */
        $remaining = [];

        foreach ($assignments as $a) {

            $remaining[$a->id] = max(
                0,
                $a->task->total_avg_time
                * max(0, ($a->quota - $a->completed_count))
            );
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

                /**
                 * Доступное время сотрудника
                 */
                $available = (int) ($wd->total_minutes ?? 0);

                /**
                 * Активные задачи
                 */
                $activeAssignments = $assignments->filter(function ($a) use ($remaining) {
                    return ($remaining[$a->id] ?? 0) > 0;
                });

                /**
                 * Сколько ВСЕГО минут требуется сотруднику
                 * на текущий момент ДО распределения
                 */
                $requiredMinutes = 0;

                foreach ($activeAssignments as $a) {
                    $requiredMinutes += ($remaining[$a->id] ?? 0);
                }

                /**
                 * Реальная загрузка сотрудника
                 *
                 * 100% = успевает
                 * 200% = нужно в 2 раза больше времени
                 * 300% = жесткая перегрузка
                 */
                $loadPercent = $available > 0
                    ? (int) round($requiredMinutes / $available * 100)
                    : 0;

                /**
                 * Если задач нет — пропускаем
                 */
                if (
                    $available > 0
                    && $activeAssignments->isNotEmpty()
                ) {

                    /**
                     * Вес каждой задачи
                     *
                     * prio1 = 1
                     * prio2 = 0.5
                     * prio3 = 0.25
                     */
                    $taskWeights = [];

                    foreach ($activeAssignments as $a) {

                        $priority = max(1, (int) $a->priority);

                        $taskWeights[$a->id] =
                            1 / (2 ** ($priority - 1));
                    }

                    $totalWeight = array_sum($taskWeights);

                    if ($totalWeight > 0) {

                        $usedMinutes = 0;

                        /**
                         * Сортируем:
                         * сначала higher priority
                         */
                        $sortedAssignments = $activeAssignments
                            ->sortBy('priority')
                            ->values();

                        foreach ($sortedAssignments as $index => $a) {

                            $weight = $taskWeights[$a->id] ?? 0;

                            if ($weight <= 0) {
                                continue;
                            }

                            /**
                             * Доля времени задачи
                             */
                            $share = $weight / $totalWeight;

                            /**
                             * Базовое распределение
                             */
                            $alloc = (int) floor(
                                $available * $share
                            );

                            /**
                             * Последней задаче отдаём остаток,
                             * чтобы сотрудник был загружен ровно на 100%
                             */
                            $isLast =
                                $index === ($sortedAssignments->count() - 1);

                            if ($isLast) {

                                $remainingDayMinutes =
                                    $available - $usedMinutes;

                                $alloc = max(
                                    $alloc,
                                    $remainingDayMinutes
                                );
                            }

                            /**
                             * Нельзя выдать больше,
                             * чем осталось по задаче
                             */
                            $alloc = min(
                                $alloc,
                                $remaining[$a->id]
                            );

                            if ($alloc <= 0) {
                                continue;
                            }

                            if (! isset($dayPlan[$a->task_id])) {
                                $dayPlan[$a->task_id] = [
                                    'minutes' => 0,
                                    'assignment_id' => $a->id,
                                ];
                            }
                            $dayPlan[$a->task_id]['minutes'] += $alloc;

                            $remaining[$a->id] -= $alloc;

                            $usedMinutes += $alloc;

                            /**
                             * Определяем:
                             * задача влезла в capacity
                             * или вызвала перегруз
                             */
                            /**
                             * Если после allocation
                             * по задаче ещё осталось время —
                             * значит задача перегружает день
                             */
                            $isOverload = ($remaining[$a->id] ?? 0) > 0;

                            $taskMeta[$a->task_id] = [
                                'minutes' => $dayPlan[$a->task_id],
                                'overload' => $isOverload,
                            ];

                            /**
                             * Daily quota
                             */
                            TaskInstance::updateOrCreate(
                                [
                                    'task_id' => $a->task_id,
                                    'date' => $dateStr,
                                ],
                                [
                                    'daily_quota' => (int) round(
                                        $alloc / max(
                                            1,
                                            $a->task->total_avg_time
                                        )
                                    ),
                                ]
                            );
                        }

                        /**
                         * Если после распределения остались минуты
                         * (из-за min remaining),
                         * докидываем их в highest priority task
                         */
                        // Весь блок leftover замените на:
                        $leftover = $available - $usedMinutes;

                        if ($leftover > 0) {
                            $topTask = $sortedAssignments
                                ->sortBy('priority')
                                ->first();

                            if ($topTask) {
                                // Проверяем существующую структуру
                                if (! isset($dayPlan[$topTask->task_id])) {
                                    $dayPlan[$topTask->task_id] = [
                                        'minutes' => 0,
                                        'assignment_id' => $topTask->id,
                                    ];
                                } elseif (! is_array($dayPlan[$topTask->task_id])) {
                                    // Если это число, конвертируем в массив
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
