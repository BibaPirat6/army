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
    /**
     * Приоритеты задач и их веса распределения времени
     */
    private const PRIORITY_WEIGHTS = [
        1 => 0.75, // 75% времени на приоритет 1
        2 => 0.20, // 20% времени на приоритет 2
        3 => 0.05, // 5% времени на приоритет 3
    ];

    /**
     * Минимальное время в минутах для задач низкого приоритета
     */
    private const MIN_LOW_PRIORITY_MINUTES = 30;

    /**
     * Генерация плана распределения задач
     * 
     * @param Employee $employee
     * @param Carbon $from Дата начала плана
     * @param Carbon $to Дата окончания плана
     * @param bool $rebuildFromToday Перестраивать ли план с сегодняшнего дня
     * @return array
     */
    public function generatePlan(
        Employee $employee, 
        Carbon $from, 
        Carbon $to,
        bool $rebuildFromToday = true
    ): array {
        // Определяем точку отсчета для построения плана
        $today = Carbon::today();
        
        // Если план на будущее — строим с начала периода
        if ($from->gt($today)) {
            $planStartDate = $from->copy();
            $rebuildFromToday = false;
        } else {
            // Строим план с сегодняшнего дня (или с начала месяца, если сегодня выходной)
            $planStartDate = $today->copy();
        }

        // 1. Получаем ВСЕ рабочие дни в периоде (и прошлые и будущие)
        $allWorkDays = WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($d) => $d->date->toDateString());

        // 2. Получаем назначения и пересчитываем остатки с учетом пропущенных дней
        $taskData = $this->calculateRemainingWorkload($employee, $from, $to, $allWorkDays, $today);

        // 3. Генерируем план начиная с сегодняшнего дня
        $plan = [];
        $current = $planStartDate->copy();

        // 4. Сначала добавляем информацию о прошедших днях (для истории)
        if ($rebuildFromToday && $from->lt($today)) {
            $historyDate = $from->copy();
            while ($historyDate->lt($today)) {
                $dateStr = $historyDate->toDateString();
                $wd = $allWorkDays->get($dateStr);
                
                // Получаем фактические данные из task_instances
                $historicalInstances = TaskInstance::where('date', $dateStr)
                    ->whereIn('task_id', array_column($taskData, 'task_id'))
                    ->get();
                
                $plan[$dateStr] = [
                    'work_day' => $wd,
                    'tasks' => [],
                    'task_meta' => [],
                    'load_percent' => 0,
                    'date' => $dateStr,
                    'is_past' => true,
                    'is_working' => $wd && $wd->type === 'рабочий_день' && $wd->work_start,
                ];

                $historyDate->addDay();
            }
        }

        // 5. Строим план на будущее (с сегодняшнего дня)
        while ($current->lte($to)) {
            $dateStr = $current->toDateString();
            $wd = $allWorkDays->get($dateStr);

            // Проверяем, не выходной ли сегодня
            $isToday = $current->isToday();
            $isWorkingDay = $wd && $wd->type === 'рабочий_день' && $wd->work_start;

            $dayPlan = $this->planDay(
                $employee,
                $current,
                $wd,
                $taskData,
                $allWorkDays,
                $isToday
            );

            $plan[$dateStr] = $dayPlan;
            $current->addDay();
        }

        // 6. Удаляем старые task_instances (которые уже в прошлом и не понадобятся)
        if ($rebuildFromToday) {
            $this->cleanupOldInstances($taskData, $today);
        }

        return [
            'plan' => $plan,
            'task_data' => $taskData,
            'employee' => $employee,
            'period' => [
                'from' => $from, 
                'to' => $to,
                'plan_start' => $planStartDate,
                'today' => $today,
            ],
            'summary' => $this->generateSummary($taskData, $today),
        ];
    }

    /**
     * Расчет оставшейся нагрузки с учетом:
     * - Пропущенных рабочих дней (болезнь, отпуск)
     * - Уже выполненных задач
     * - Прошедших дней периода
     */
    private function calculateRemainingWorkload(
        Employee $employee,
        Carbon $from,
        Carbon $to,
        $allWorkDays,
        Carbon $today
    ): array {
        // Получаем все назначения
        $assignments = TaskAssignment::with(['task.subtasks'])
            ->where('employee_id', $employee->id)
            ->whereHas('task', function ($q) use ($from, $to) {
                $q->where(function ($sub) use ($from, $to) {
                    $sub->where('start_date', '<=', $to)
                        ->where('end_date', '>=', $from);
                });
            })
            ->get();

        $taskData = [];

        foreach ($assignments as $assignment) {
            $task = $assignment->task;
            $iterationTime = $task->subtasks->sum('avg_time_minutes') ?: 60;
            
            // Базовая информация
            $totalQuota = (int) $assignment->quota;
            $completedCount = (int) $assignment->completed_count;
            $remainingQuota = max(0, $totalQuota - $completedCount);
            
            // Рассчитываем, сколько рабочих дней уже прошло и сколько осталось
            $taskStart = Carbon::parse($task->start_date);
            $taskEnd = Carbon::parse($task->end_date);
            
            // Считаем прошедшие рабочие дни с начала задачи до вчерашнего дня
            $pastWorkDays = $this->countPastWorkDays($taskStart, $today, $allWorkDays, $employee);
            
            // Считаем оставшиеся рабочие дни (с сегодня до конца задачи)
            $remainingWorkDays = $this->countRemainingWorkDays($today, $taskEnd, $allWorkDays, $employee);
            
            // Рассчитываем, сколько должно было быть выполнено на сегодня
            $totalWorkDays = $pastWorkDays + $remainingWorkDays;
            $expectedQuotaPerDay = $totalWorkDays > 0 ? $totalQuota / $totalWorkDays : $totalQuota;
            $expectedCompleted = (int) round($expectedQuotaPerDay * $pastWorkDays);
            
            // Если сотрудник пропустил дни (болел), пересчитываем
            $actualMissedQuota = max(0, $expectedCompleted - $completedCount);
            
            // Добавляем пропущенную квоту к оставшейся
            $adjustedRemainingQuota = $remainingQuota + $actualMissedQuota;
            
            // Пересчитываем оставшиеся минуты
            $remainingMinutes = $adjustedRemainingQuota * $iterationTime;
            
            // Рассчитываем новую дневную норму для наверстывания
            $newDailyQuota = $remainingWorkDays > 0 
                ? ceil($adjustedRemainingQuota / $remainingWorkDays) 
                : $adjustedRemainingQuota;
            
            // Проверяем статус задачи
            $isOverdue = $pastWorkDays > 0 && $completedCount < $expectedCompleted;
            $isOnTrack = !$isOverdue && $remainingQuota <= $remainingWorkDays * $newDailyQuota;
            
            $priority = (int) ($assignment->priority ?? 3);
            $priority = in_array($priority, [1, 2, 3]) ? $priority : 3;

            $taskData[$assignment->id] = [
                'assignment' => $assignment,
                'task_id' => $task->id,
                'priority' => $priority,
                'iteration_time' => $iterationTime,
                'total_quota' => $totalQuota,
                'completed_count' => $completedCount,
                'remaining_quota_original' => $remainingQuota,
                'adjusted_remaining_quota' => $adjustedRemainingQuota,
                'remaining_minutes' => $remainingMinutes,
                'task_start' => $taskStart,
                'task_end' => $taskEnd,
                'task_name' => $task->title,
                'task_name_short' => $this->truncateText($task->title, 30),
                'is_completed' => $remainingQuota <= 0,
                'is_overdue' => $isOverdue,
                'is_on_track' => $isOnTrack,
                'past_work_days' => $pastWorkDays,
                'remaining_work_days' => $remainingWorkDays,
                'expected_completed' => $expectedCompleted,
                'actual_missed' => $actualMissedQuota,
                'new_daily_quota' => $newDailyQuota,
            ];
        }

        // Сортируем: сначала просроченные, потом по приоритету, потом по дедлайну
        uasort($taskData, function ($a, $b) {
            // Просроченные всегда первые
            if ($a['is_overdue'] && !$b['is_overdue']) return -1;
            if (!$a['is_overdue'] && $b['is_overdue']) return 1;
            
            // Затем по приоритету
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] - $b['priority'];
            }
            
            // Затем по дедлайну (ближайший первый)
            return $a['task_end']->timestamp - $b['task_end']->timestamp;
        });

        return $taskData;
    }

    /**
     * Подсчет прошедших рабочих дней в периоде задачи
     */
    private function countPastWorkDays(Carbon $taskStart, Carbon $today, $allWorkDays, Employee $employee): int
    {
        $count = 0;
        $date = $taskStart->copy();
        $endDate = $today->copy()->subDay(); // До вчерашнего дня
        
        while ($date->lte($endDate)) {
            $dateStr = $date->toDateString();
            
            // Проверяем, есть ли запись в work_days
            if (isset($allWorkDays[$dateStr])) {
                $wd = $allWorkDays[$dateStr];
                if ($wd->type === 'рабочий_день' && $wd->work_start) {
                    $count++;
                }
            } else {
                // Если записи нет, проверяем в БД
                $wd = WorkDay::where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->first();
                    
                if ($wd && $wd->type === 'рабочий_день' && $wd->work_start) {
                    $count++;
                }
            }
            
            $date->addDay();
        }
        
        return $count;
    }

    /**
     * Подсчет оставшихся рабочих дней
     */
    private function countRemainingWorkDays(Carbon $today, Carbon $taskEnd, $allWorkDays, Employee $employee): int
    {
        $count = 0;
        $date = $today->copy();
        
        while ($date->lte($taskEnd)) {
            $dateStr = $date->toDateString();
            
            if (isset($allWorkDays[$dateStr])) {
                $wd = $allWorkDays[$dateStr];
                if ($wd->type === 'рабочий_день' && $wd->work_start) {
                    $count++;
                }
            } else {
                $wd = WorkDay::where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->first();
                    
                if ($wd && $wd->type === 'рабочий_день' && $wd->work_start) {
                    $count++;
                }
            }
            
            $date->addDay();
        }
        
        return max(1, $count); // Минимум 1 день, чтобы не делить на 0
    }

    /**
     * Планирование одного дня
     */
    private function planDay(
        Employee $employee,
        Carbon $currentDate,
        $workDay,
        array &$taskData,
        $allWorkDays,
        bool $isToday = false
    ): array {
        $dateStr = $currentDate->toDateString();

        $dayPlan = [
            'work_day' => $workDay,
            'tasks' => [],
            'task_meta' => [],
            'load_percent' => 0,
            'date' => $dateStr,
            'is_today' => $isToday,
            'is_past' => false,
        ];

        // Если нерабочий день — возвращаем пустой план
        if (!$workDay || $workDay->type !== 'рабочий_день' || !$workDay->work_start) {
            return $dayPlan;
        }

        $totalAvailableMinutes = (int) ($workDay->total_minutes ?? 480);
        $usedMinutes = 0;

        // Сначала обрабатываем просроченные задачи (они в приоритете)
        $overdueTasks = $this->getOverdueTasks($taskData, $currentDate);
        
        if (!empty($overdueTasks)) {
            // На просроченные задачи выделяем до 80% времени
            $overdueTime = (int) ($totalAvailableMinutes * 0.8);
            $usedMinutes += $this->distributeTimeAmongTasks(
                $overdueTasks,
                $overdueTime,
                $currentDate,
                $allWorkDays,
                $dayPlan,
                $taskData,
                $employee,
                true // isOverdue
            );
        }

        // Затем распределяем оставшееся время по приоритетам
        foreach ([1, 2, 3] as $priority) {
            $priorityTime = $this->calculatePriorityTime(
                $priority,
                $totalAvailableMinutes,
                $usedMinutes,
                $taskData,
                $currentDate
            );

            if ($priorityTime <= 0) {
                continue;
            }

            $activeTasks = $this->getActiveTasksForPriority($taskData, $priority, $currentDate);

            if (empty($activeTasks)) {
                continue;
            }

            $usedMinutes += $this->distributeTimeAmongTasks(
                $activeTasks,
                $priorityTime,
                $currentDate,
                $allWorkDays,
                $dayPlan,
                $taskData,
                $employee
            );
        }

        // Если сегодня и осталось время — добавляем на просроченные задачи
        if ($isToday && $usedMinutes < $totalAvailableMinutes && !empty($overdueTasks)) {
            $extraTime = $totalAvailableMinutes - $usedMinutes;
            $this->distributeTimeAmongTasks(
                $overdueTasks,
                $extraTime,
                $currentDate,
                $allWorkDays,
                $dayPlan,
                $taskData,
                $employee,
                true
            );
        }

        // Рассчитываем процент загрузки
        if ($totalAvailableMinutes > 0) {
            $dayPlan['load_percent'] = (int) round(($usedMinutes / $totalAvailableMinutes) * 100);
        }

        // Сохраняем TaskInstance для будущих дней
        if (!$currentDate->isPast() || $isToday) {
            $this->saveTaskInstances($dayPlan, $taskData, $dateStr);
        }

        return $dayPlan;
    }

    /**
     * Получить просроченные задачи
     */
    private function getOverdueTasks(array &$taskData, Carbon $currentDate): array
    {
        $overdue = [];

        foreach ($taskData as $id => &$data) {
            if ($data['is_completed']) {
                continue;
            }

            // Проверяем, что задача активна
            if ($currentDate->lt($data['task_start']) || $currentDate->gt($data['task_end'])) {
                continue;
            }

            if ($data['remaining_minutes'] <= 0) {
                continue;
            }

            // Задача просрочена или отстает от графика
            if ($data['is_overdue'] || $data['actual_missed'] > 0) {
                $overdue[$id] = &$data;
            }
        }

        // Сортируем: самые просроченные первые
        uasort($overdue, function ($a, $b) {
            $aUrgency = $a['actual_missed'] / max(1, $a['remaining_work_days']);
            $bUrgency = $b['actual_missed'] / max(1, $b['remaining_work_days']);
            return $bUrgency - $aUrgency;
        });

        return $overdue;
    }

    /**
     * Расчет доступного времени для приоритета
     */
    private function calculatePriorityTime(
        int $priority,
        int $totalAvailable,
        int $alreadyUsed,
        array $taskData,
        Carbon $currentDate
    ): int {
        $remaining = max(0, $totalAvailable - $alreadyUsed);

        // Приоритет 1 — максимум времени
        if ($priority === 1) {
            $hasPriority1Tasks = !empty($this->getActiveTasksForPriority($taskData, 1, $currentDate));
            
            if ($hasPriority1Tasks) {
                return (int) ($remaining * 0.85); // 85% от оставшегося
            }
        }

        // Приоритет 2 — что осталось после приоритета 1
        if ($priority === 2) {
            return (int) ($remaining * 0.7); // 70% от оставшегося
        }

        // Приоритет 3 — остатки
        if ($priority === 3) {
            return min($remaining, self::MIN_LOW_PRIORITY_MINUTES);
        }

        return 0;
    }

    /**
     * Получить активные задачи для приоритета
     */
    private function getActiveTasksForPriority(array &$taskData, int $priority, Carbon $date): array
    {
        $activeTasks = [];

        foreach ($taskData as $id => &$data) {
            if ($data['priority'] !== $priority) {
                continue;
            }

            if ($data['is_completed']) {
                continue;
            }

            if ($date->lt($data['task_start']) || $date->gt($data['task_end'])) {
                continue;
            }

            if ($data['remaining_minutes'] <= 0) {
                continue;
            }

            // Не включаем просроченные (они обрабатываются отдельно)
            if ($data['is_overdue']) {
                continue;
            }

            $activeTasks[$id] = &$data;
        }

        return $activeTasks;
    }

    /**
     * Распределение времени между задачами
     */
    private function distributeTimeAmongTasks(
        array &$tasks,
        int $availableTime,
        Carbon $currentDate,
        $allWorkDays,
        array &$dayPlan,
        array &$taskData,
        Employee $employee,
        bool $isOverdue = false
    ): int {
        $usedTime = 0;

        if (empty($tasks) || $availableTime <= 0) {
            return 0;
        }

        // Для одной задачи — отдаем всё
        if (count($tasks) === 1) {
            $taskId = array_key_first($tasks);
            $task = &$tasks[$taskId];
            
            $allocatedTime = min($task['remaining_minutes'], $availableTime);
            $allocatedTime = (int) floor($allocatedTime);
            
            if ($allocatedTime > 0) {
                $this->addTaskToDayPlan($dayPlan, $task, $allocatedTime, $currentDate, $allWorkDays, $employee, $isOverdue);
                $task['remaining_minutes'] -= $allocatedTime;
                
                // Уменьшаем количество пропущенной квоты
                $iterationTime = $task['iteration_time'];
                $completedNow = (int) round($allocatedTime / $iterationTime);
                $task['actual_missed'] = max(0, $task['actual_missed'] - $completedNow);
                
                // Проверяем, не наверстали ли мы отставание
                if ($task['actual_missed'] <= 0) {
                    $task['is_overdue'] = false;
                }
                
                $usedTime = $allocatedTime;
            }
            
            return $usedTime;
        }

        // Для нескольких задач — пропорционально
        $totalRemaining = array_sum(array_column($tasks, 'remaining_minutes'));
        
        if ($totalRemaining <= 0) {
            return 0;
        }

        $remainingToDistribute = $availableTime;

        foreach ($tasks as $id => &$task) {
            if ($remainingToDistribute <= 0) {
                break;
            }

            $proportion = $task['remaining_minutes'] / $totalRemaining;
            $allocatedTime = (int) floor($availableTime * $proportion);
            $allocatedTime = min($allocatedTime, $task['remaining_minutes'], $remainingToDistribute);
            $allocatedTime = max(0, $allocatedTime);

            if ($allocatedTime > 0) {
                $this->addTaskToDayPlan($dayPlan, $task, $allocatedTime, $currentDate, $allWorkDays, $employee, $isOverdue);
                $task['remaining_minutes'] -= $allocatedTime;
                
                $iterationTime = $task['iteration_time'];
                $completedNow = (int) round($allocatedTime / $iterationTime);
                $task['actual_missed'] = max(0, $task['actual_missed'] - $completedNow);
                
                if ($task['actual_missed'] <= 0) {
                    $task['is_overdue'] = false;
                }
                
                $usedTime += $allocatedTime;
                $remainingToDistribute -= $allocatedTime;
            }
        }

        // Остаток отдаем самой просроченной/приоритетной
        if ($remainingToDistribute > 0 && !empty($tasks)) {
            $firstTask = &$tasks[array_key_first($tasks)];
            $extraTime = min($remainingToDistribute, $firstTask['remaining_minutes']);
            
            if ($extraTime > 0) {
                $this->addTaskToDayPlan($dayPlan, $firstTask, $extraTime, $currentDate, $allWorkDays, $employee, $isOverdue);
                $firstTask['remaining_minutes'] -= $extraTime;
                $usedTime += $extraTime;
            }
        }

        return $usedTime;
    }

    /**
     * Добавить задачу в дневной план
     */
    private function addTaskToDayPlan(
        array &$dayPlan,
        array $task,
        int $minutes,
        Carbon $currentDate,
        $allWorkDays,
        Employee $employee,
        bool $isOverdue = false
    ): void {
        $taskId = $task['task_id'];
        $assignmentId = $task['assignment']->id;
        $iterationTime = $task['iteration_time'];

        if (!isset($dayPlan['tasks'][$taskId])) {
            $dayPlan['tasks'][$taskId] = [
                'minutes' => 0,
                'assignment_id' => $assignmentId,
            ];
        }
        $dayPlan['tasks'][$taskId]['minutes'] += $minutes;

        $dailyQuota = (int) round($minutes / $iterationTime);

        $dayPlan['task_meta'][$taskId] = [
            'minutes' => $minutes,
            'daily_quota' => $dailyQuota,
            'task_name' => $task['task_name'],
            'task_name_short' => $task['task_name_short'],
            'task_total_minutes' => $task['total_quota'] * $iterationTime,
            'remaining_minutes' => $task['remaining_minutes'],
            'quota_total' => $task['total_quota'],
            'completed_count' => $task['completed_count'],
            'remaining_quota' => max(0, (int) ceil($task['remaining_minutes'] / $iterationTime)),
            'adjusted_remaining_quota' => $task['adjusted_remaining_quota'] ?? $task['remaining_quota_original'],
            'start_date' => $task['task_start']->format('d.m.Y'),
            'end_date' => $task['task_end']->format('d.m.Y'),
            'task_id' => $taskId,
            'iteration_time' => $iterationTime,
            'priority' => $task['priority'],
            'is_overdue' => $isOverdue || ($task['is_overdue'] ?? false),
            'actual_missed' => $task['actual_missed'] ?? 0,
            'remaining_work_days' => $task['remaining_work_days'] ?? 0,
        ];
    }

    /**
     * Генерация сводки
     */
    private function generateSummary(array $taskData, Carbon $today): array
    {
        $summary = [
            'total_tasks' => count($taskData),
            'overdue_tasks' => 0,
            'on_track_tasks' => 0,
            'completed_tasks' => 0,
            'total_remaining_minutes' => 0,
            'total_missed_quota' => 0,
        ];

        foreach ($taskData as $data) {
            if ($data['is_completed']) {
                $summary['completed_tasks']++;
            } else {
                $summary['total_remaining_minutes'] += $data['remaining_minutes'];
                $summary['total_missed_quota'] += $data['actual_missed'];
                
                if ($data['is_overdue']) {
                    $summary['overdue_tasks']++;
                } elseif ($data['is_on_track']) {
                    $summary['on_track_tasks']++;
                }
            }
        }

        return $summary;
    }

    /**
     * Сохранение TaskInstance
     */
    private function saveTaskInstances(array $dayPlan, array $taskData, string $dateStr): void
    {
        foreach ($dayPlan['tasks'] as $taskId => $data) {
            $taskInfo = null;
            
            foreach ($taskData as $tData) {
                if ($tData['task_id'] === $taskId) {
                    $taskInfo = $tData;
                    break;
                }
            }

            if (!$taskInfo) {
                continue;
            }

            $iterationTime = $taskInfo['iteration_time'];
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
    }

    /**
     * Очистка старых инстансов
     */
    private function cleanupOldInstances(array $taskData, Carbon $today): void
    {
        $taskIds = array_column($taskData, 'task_id');
        
        TaskInstance::whereIn('task_id', $taskIds)
            ->where('date', '<', $today->format('Y-m-d'))
            ->delete();
    }

    // ... остальные вспомогательные методы ...
    
    private function truncateText($text, $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }
}