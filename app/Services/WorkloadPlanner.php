<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TaskInstance;
use App\Models\WorkDay;
use Carbon\Carbon;
use DB;
use Log;

class WorkloadPlanner
{
    public function generatePlan(Employee $employee, Carbon $from, Carbon $to): array
    {
        // 1. Получаем рабочие дни
        $workDays = WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->get()->keyBy(fn ($d) => $d->date->toDateString());

        // 2. Получаем назначения с подзадачами, УЧИТЫВАЯ ПЕРИОД ДЕЙСТВИЯ ЗАДАЧИ
        $assignments = TaskAssignment::with(['task.subtasks'])
            ->where('employee_id', $employee->id)
            ->whereHas('task', function ($q) use ($from, $to) {
                $q->where(function ($sub) use ($from, $to) {
                    $sub->where('start_date', '<=', $to)
                        ->where('end_date', '>=', $from);
                });
            })
            ->get()
            ->sortBy('priority')
            ->values();

        // 3. Предварительный расчет
        $remainingMinutesMap = [];
        $taskIterationTimeMap = [];
        $taskDateRangeMap = [];

        foreach ($assignments as $a) {
            $taskDateRangeMap[$a->task_id] = [
                'start' => Carbon::parse($a->task->start_date),
                'end' => Carbon::parse($a->task->end_date),
            ];

            $iterationTime = $a->task->subtasks->sum('avg_time_minutes');
            if ($iterationTime <= 0) {
                $iterationTime = 60;
            }
            $taskIterationTimeMap[$a->task_id] = $iterationTime;

            $remainingQuota = max(0, $a->quota - $a->completed_count);
            $remainingMinutesMap[$a->id] = $remainingQuota * $iterationTime;
        }

        $plan = [];
        $current = $from->copy();

        while ($current->lte($to)) {
            $dateStr = $current->toDateString();
            $wd = $workDays->get($dateStr);

            $dayPlan = [];
            $taskMeta = [];
            $loadPercent = 0;

            if ($wd && $wd->type === 'рабочий_день' && $wd->work_start) {
                $availableMinutes = (int) ($wd->total_minutes ?? 0);
                $usedMinutes = 0;

                foreach ($assignments as $a) {
                    // Проверка периода действия задачи
                    $taskRange = $taskDateRangeMap[$a->task_id] ?? null;
                    $currentDate = $current->copy();

                    if (! $taskRange ||
                        $currentDate->lt($taskRange['start']) ||
                        $currentDate->gt($taskRange['end'])) {
                        continue;
                    }

                    if (($remainingMinutesMap[$a->id] ?? 0) <= 0) {
                        continue;
                    }

                    $timeLeftInDay = $availableMinutes - $usedMinutes;
                    if ($timeLeftInDay <= 0) {
                        break;
                    }

                    $iterationTime = $taskIterationTimeMap[$a->task_id];

                    // Равномерное распределение по оставшимся дням
                    $daysLeftInTaskPeriod = $this->getWorkingDaysLeftInPeriod(
                        $employee,
                        $currentDate,
                        $taskRange['end'],
                        $workDays
                    );

                    $maxTimeToday = $daysLeftInTaskPeriod > 0
                        ? ceil($remainingMinutesMap[$a->id] / $daysLeftInTaskPeriod)
                        : $remainingMinutesMap[$a->id];

                    $alloc = min($remainingMinutesMap[$a->id], $timeLeftInDay, $maxTimeToday);
                    $alloc = (int) floor($alloc);

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

                    $remainingMinutesMap[$a->id] -= $alloc;
                    $usedMinutes += $alloc;

                    // Рассчитываем дневную квоту (количество итераций)
                    $dailyQuota = (int) round($alloc / $iterationTime);

                    $taskMeta[$a->task_id] = [
                        'minutes' => $alloc,
                        'daily_quota' => $dailyQuota,
                        'task_name' => $a->task->title,
                        'task_name_short' => $this->truncateText($a->task->title, 30),
                        'task_total_minutes' => $a->quota * $iterationTime,
                        'remaining_minutes' => $remainingMinutesMap[$a->id],
                        'quota_total' => $a->quota,
                        'completed_count' => $a->completed_count,
                        'remaining_quota' => max(0, ceil($remainingMinutesMap[$a->id] / $iterationTime)),
                        'start_date' => $taskRange['start']->format('d.m.Y'),
                        'end_date' => $taskRange['end']->format('d.m.Y'),
                        'task_id' => $a->task_id,
                        'iteration_time' => $iterationTime,
                    ];
                }

                if ($availableMinutes > 0) {
                    $loadPercent = (int) round(($usedMinutes / $availableMinutes) * 100);
                }
            }

            // Сохраняем TaskInstance - только daily_quota, остальное вычисляется
            foreach ($dayPlan as $taskId => $data) {
                $iterationTime = $taskIterationTimeMap[$taskId] ?? 1;
                $dailyQuota = (int) round($data['minutes'] / $iterationTime);

                if ($dailyQuota > 0) {
                    TaskInstance::updateOrCreate(
                        [
                            'task_id' => $taskId,
                            'date' => $dateStr,
                        ],
                        [
                            'daily_quota' => $dailyQuota,
                        ]
                    );
                }
            }

            $plan[$dateStr] = [
                'work_day' => $wd,
                'tasks' => $dayPlan,
                'task_meta' => $taskMeta,
                'load_percent' => $loadPercent,
                'date' => $dateStr,
            ];

            $current->addDay();
        }

        return [
            'plan' => $plan,
            'remaining' => $remainingMinutesMap,
            'employee' => $employee,
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    /**
     * Подсчет оставшихся рабочих дней в периоде задачи
     */
    private function getWorkingDaysLeftInPeriod(Employee $employee, Carbon $currentDate, Carbon $endDate, $workDays): int
    {
        $daysCount = 0;
        $date = $currentDate->copy();

        while ($date->lte($endDate)) {
            $dateStr = $date->toDateString();
            $wd = $workDays->get($dateStr);

            if ($wd && $wd->type === 'рабочий_день' && $wd->work_start) {
                $daysCount++;
            }

            $date->addDay();
        }

        return max(1, $daysCount);
    }

    private function truncateText($text, $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'...';
    }

    /**
     * Перераспределить нагрузку для сотрудника
     */
    public function redistributeForEmployee(Employee $employee, Carbon $from): void
    {
        $to = $from->copy()->addMonths(3);

        $workDays = WorkDay::where('employee_id', $employee->id)
            ->where('date', '>=', $from->startOfDay())
            ->where('date', '<=', $to->endOfDay())
            ->orderBy('date')
            ->get();

        if ($workDays->isEmpty()) {
            return;
        }

        $assignments = TaskAssignment::with(['task.subtasks'])
            ->where('employee_id', $employee->id)
            ->where('completed_count', '<', \DB::raw('quota'))
            ->whereHas('task', function ($q) use ($from) {
                $q->where('end_date', '>=', $from);
            })
            ->get()
            ->sortBy('priority');

        $taskData = [];
        foreach ($assignments as $a) {
            $iterationTime = $a->task->subtasks->sum('avg_time_minutes') ?: 60;
            $remainingMinutes = ($a->quota - $a->completed_count) * $iterationTime;

            $taskData[$a->id] = [
                'assignment' => $a,
                'remaining_minutes' => $remainingMinutes,
                'iteration_time' => $iterationTime,
                'task_start' => Carbon::parse($a->task->start_date),
                'task_end' => Carbon::parse($a->task->end_date),
                'task_id' => $a->task_id,
            ];
        }

        foreach ($workDays as $day) {
            if ($day->type !== 'рабочий_день' || ! $day->total_minutes) {
                continue;
            }

            $available = (int) $day->total_minutes;
            $used = 0;
            $currentDate = Carbon::parse($day->date);

            foreach ($taskData as $id => &$data) {
                if ($data['remaining_minutes'] <= 0) {
                    continue;
                }

                if ($currentDate->lt($data['task_start']) || $currentDate->gt($data['task_end'])) {
                    continue;
                }

                $left = $available - $used;
                if ($left <= 0) {
                    break;
                }

                $daysLeft = $this->getDaysLeftInPeriod($currentDate, $data['task_end'], $workDays);
                $maxPerDay = $daysLeft > 0 ? ceil($data['remaining_minutes'] / $daysLeft) : $data['remaining_minutes'];

                $alloc = min($data['remaining_minutes'], $left, $maxPerDay);
                $alloc = (int) floor($alloc);

                if ($alloc <= 0) {
                    continue;
                }

                $dailyQuota = (int) round($alloc / $data['iteration_time']);

                if ($dailyQuota > 0) {
                    TaskInstance::updateOrCreate(
                        [
                            'task_id' => $data['task_id'],
                            'date' => $day->date->format('Y-m-d'),
                        ],
                        [
                            'daily_quota' => $dailyQuota,
                        ]
                    );
                }

                $data['remaining_minutes'] -= $alloc;
                $used += $alloc;
            }
        }
    }

    private function getDaysLeftInPeriod(Carbon $currentDate, Carbon $endDate, $workDays): int
    {
        $days = 0;
        $date = $currentDate->copy();

        while ($date->lte($endDate)) {
            $wd = $workDays->firstWhere('date', $date->toDateString());
            if ($wd && $wd->type === 'рабочий_день' && $wd->work_start) {
                $days++;
            }
            $date->addDay();
        }

        return max(1, $days);
    }

    /**
     * Получить запланированные минуты для задачи в конкретный день
     */
    public function getPlannedMinutesForDay(TaskInstance $instance): int
    {
        $iterationTime = $instance->task->subtasks->sum('avg_time_minutes') ?: 60;

        return $instance->daily_quota * $iterationTime;
    }

    /**
     * Перераспределение нагрузки после частичного выполнения
     */
    public function redistributeAfterCompletion(TaskAssignment $assignment): void
    {
        try {
            DB::beginTransaction();

            $employee = $assignment->employee;
            $task = $assignment->task;

            // Оставшаяся квота
            $remainingQuota = max(0, $assignment->quota - $assignment->completed_count);

            if ($remainingQuota <= 0) {
                DB::commit();

                return;
            }

            // Начинаем перераспределение с завтрашнего дня
            $from = Carbon::tomorrow();
            $to = Carbon::parse($task->end_date);

            if ($from->gt($to)) {
                DB::commit();

                return;
            }

            // Получаем оставшиеся рабочие дни
            $workDays = WorkDay::where('employee_id', $employee->id)
                ->where('date', '>=', $from->format('Y-m-d'))
                ->where('date', '<=', $to->format('Y-m-d'))
                ->where('type', 'рабочий_день')
                ->orderBy('date')
                ->get();

            if ($workDays->isEmpty()) {
                DB::commit();

                return;
            }

            // Удаляем старые распределения для этой задачи
            TaskInstance::where('task_id', $task->id)
                ->where('date', '>=', $from->format('Y-m-d'))
                ->delete();

            // Рассчитываем новое распределение
            $iterationTime = $task->subtasks->sum('avg_time_minutes') ?: 60;
            $totalRemainingMinutes = $remainingQuota * $iterationTime;
            $daysCount = $workDays->count();

            $minutesPerDay = (int) ceil($totalRemainingMinutes / $daysCount);
            $distributedMinutes = 0;

            foreach ($workDays as $index => $workDay) {
                $availableMinutes = (int) ($workDay->total_minutes ?? 480);

                // Последний день - распределяем остаток
                if ($index === $daysCount - 1) {
                    $minutesForDay = $totalRemainingMinutes - $distributedMinutes;
                } else {
                    $minutesForDay = min($minutesPerDay, $totalRemainingMinutes - $distributedMinutes);
                }

                $minutesForDay = min($minutesForDay, $availableMinutes);
                $minutesForDay = max(0, $minutesForDay);

                if ($minutesForDay <= 0) {
                    continue;
                }

                $dailyQuota = (int) ceil($minutesForDay / $iterationTime);

                TaskInstance::updateOrCreate(
                    [
                        'task_id' => $task->id,
                        'date' => $workDay->date->format('Y-m-d'),
                    ],
                    [
                        'daily_quota' => $dailyQuota,
                    ]
                );

                $distributedMinutes += $minutesForDay;
            }

            DB::commit();

            Log::info('Нагрузка перераспределена после выполнения', [
                'assignment_id' => $assignment->id,
                'remaining_quota' => $remainingQuota,
                'days_count' => $daysCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Ошибка перераспределения нагрузки', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
