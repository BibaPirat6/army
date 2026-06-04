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
     * Коэффициент заполнения дня (95% - 5% на отдых)
     */
    private const DAY_FILL_RATE = 0.95;

    /**
     * Минимальное время на задачу в минутах
     */
    private const MIN_TASK_MINUTES = 15;

    /**
     * Генерация плана распределения задач
     */
    public function generatePlan(
        Employee $employee,
        Carbon $from,
        Carbon $to,
        bool $rebuildFromToday = true
    ): array {
        $today = Carbon::today();

        // Определяем точку отсчета
        $planStartDate = $from->gt($today) ? $from->copy() : $today->copy();

        // 1. Получаем рабочие дни
        $allWorkDays = $this->getWorkDays($employee, $from, $to);

        // 2. Рассчитываем нагрузку с учетом пропущенных дней
        $taskData = $this->calculateRemainingWorkload($employee, $from, $to, $allWorkDays, $today);

        // 3. Генерируем план с сегодняшнего дня
        $plan = [];
        $current = $planStartDate->copy();

        // Добавляем историю (прошедшие дни)
        if ($from->lt($today)) {
            $this->addHistoricalDays($plan, $from, $today, $allWorkDays, $taskData);
        }

        // Планируем будущие дни
        while ($current->lte($to)) {
            $dateStr = $current->toDateString();
            $wd = $allWorkDays->get($dateStr);

            $plan[$dateStr] = $this->planDay(
                $employee,
                $current,
                $wd,
                $taskData,
                $allWorkDays
            );

            $current->addDay();
        }

        // Очищаем старые инстансы
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
     * Получить рабочие дни
     */
    private function getWorkDays(Employee $employee, Carbon $from, Carbon $to)
    {
        return WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get()
            ->keyBy(fn($d) => $d->date->toDateString());
    }

    /**
     * Расчет оставшейся нагрузки
     */
    private function calculateRemainingWorkload(
        Employee $employee,
        Carbon $from,
        Carbon $to,
        $allWorkDays,
        Carbon $today
    ): array {
        $assignments = TaskAssignment::with(['task.subtasks'])
            ->where('employee_id', $employee->id)
            ->whereHas('task', function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from);
            })
            ->get();

        $taskData = [];

        foreach ($assignments as $assignment) {
            $task = $assignment->task;
            
            // Время одной итерации (сумма подзадач)
            $iterationTime = $task->subtasks->sum('avg_time_minutes');
            if ($iterationTime <= 0) {
                $iterationTime = 60; // По умолчанию 1 час
            }

            $totalQuota = (int)$assignment->quota;
            $completedCount = (int)$assignment->completed_count;
            $remainingQuota = max(0, $totalQuota - $completedCount);

            $taskStart = Carbon::parse($task->start_date);
            $taskEnd = Carbon::parse($task->end_date);

            // Считаем рабочие дни
            $pastWorkDays = $this->countWorkDays($taskStart, $today->copy()->subDay(), $allWorkDays, $employee);
            $remainingWorkDays = $this->countWorkDays($today, $taskEnd, $allWorkDays, $employee);
            $totalWorkDays = $pastWorkDays + $remainingWorkDays;

            // Сколько должно было быть выполнено
            $expectedCompleted = $totalWorkDays > 0
                ? (int)round(($totalQuota / $totalWorkDays) * $pastWorkDays)
                : 0;

            // Пропущенная квота
            $missedQuota = max(0, $expectedCompleted - $completedCount);
            $adjustedRemainingQuota = $remainingQuota + $missedQuota;

            // Оставшиеся минуты
            $remainingMinutes = $adjustedRemainingQuota * $iterationTime;

            // Дневная норма
            $newDailyQuota = $remainingWorkDays > 0
                ? (int)ceil($adjustedRemainingQuota / $remainingWorkDays)
                : $adjustedRemainingQuota;

            // Минимальное время на задачу в день = одна итерация
            $minDailyMinutes = $iterationTime;

            $priority = (int)($assignment->priority ?? 3);
            $priority = in_array($priority, [1, 2, 3]) ? $priority : 3;

            $taskData[$assignment->id] = [
                'assignment_id' => $assignment->id,
                'assignment' => $assignment,
                'task_id' => $task->id,
                'task_name' => $task->title,
                'task_name_short' => $this->truncateText($task->title, 30),
                'priority' => $priority,
                'iteration_time' => $iterationTime,
                'total_quota' => $totalQuota,
                'completed_count' => $completedCount,
                'remaining_quota' => $remainingQuota,
                'adjusted_remaining_quota' => $adjustedRemainingQuota,
                'remaining_minutes' => $remainingMinutes,
                'min_daily_minutes' => $minDailyMinutes,
                'task_start' => $taskStart,
                'task_end' => $taskEnd,
                'is_completed' => $remainingQuota <= 0,
                'is_overdue' => $pastWorkDays > 0 && $completedCount < $expectedCompleted,
                'missed_quota' => $missedQuota,
                'past_work_days' => $pastWorkDays,
                'remaining_work_days' => $remainingWorkDays,
                'expected_completed' => $expectedCompleted,
                'new_daily_quota' => $newDailyQuota,
            ];
        }

        // Сортировка: просроченные → приоритет 1 → приоритет 2 → приоритет 3 → по дедлайну
        uasort($taskData, function ($a, $b) {
            // Просроченные первые
            if ($a['is_overdue'] && !$b['is_overdue']) return -1;
            if (!$a['is_overdue'] && $b['is_overdue']) return 1;

            // По приоритету
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] - $b['priority'];
            }

            // По дедлайну
            return $a['task_end']->timestamp - $b['task_end']->timestamp;
        });

        return $taskData;
    }

    /**
     * Подсчет рабочих дней в периоде
     */
    private function countWorkDays(Carbon $start, Carbon $end, $cachedWorkDays, Employee $employee): int
    {
        $count = 0;
        $date = $start->copy();

        while ($date->lte($end)) {
            $dateStr = $date->toDateString();

            if (isset($cachedWorkDays[$dateStr])) {
                $wd = $cachedWorkDays[$dateStr];
                if ($wd->type === 'рабочий_день' && $wd->work_start) {
                    $count++;
                }
            }
            $date->addDay();
        }

        return max(1, $count);
    }

    /**
     * Добавить исторические дни
     */
    private function addHistoricalDays(array &$plan, Carbon $from, Carbon $today, $allWorkDays, array $taskData): void
    {
        $date = $from->copy();
        while ($date->lt($today)) {
            $dateStr = $date->toDateString();
            $wd = $allWorkDays->get($dateStr);

            $plan[$dateStr] = [
                'work_day' => $wd,
                'tasks' => [],
                'task_meta' => [],
                'load_percent' => 0,
                'date' => $dateStr,
                'is_past' => true,
                'is_today' => false,
            ];

            $date->addDay();
        }
    }

    /**
     * Планирование одного дня
     */
    private function planDay(
        Employee $employee,
        Carbon $currentDate,
        $workDay,
        array &$taskData,
        $allWorkDays
    ): array {
        $dateStr = $currentDate->toDateString();
        $isToday = $currentDate->isToday();

        $dayPlan = [
            'work_day' => $workDay,
            'tasks' => [],
            'task_meta' => [],
            'load_percent' => 0,
            'date' => $dateStr,
            'is_today' => $isToday,
            'is_past' => false,
        ];

        // Если нерабочий день
        if (!$workDay || $workDay->type !== 'рабочий_день' || !$workDay->work_start) {
            return $dayPlan;
        }

        // Доступное время с учетом 5% на отдых
        $totalMinutes = (int)($workDay->total_minutes ?? 480);
        $availableMinutes = (int)($totalMinutes * self::DAY_FILL_RATE);
        $usedMinutes = 0;

        // 1. Получаем активные задачи на эту дату
        $activeTasks = $this->getActiveTasksForDate($taskData, $currentDate);

        if (empty($activeTasks)) {
            return $dayPlan;
        }

        // 2. Группируем задачи по приоритетам
        $priorityGroups = $this->groupTasksByPriority($activeTasks);

        // 3. Распределяем время по приоритетам
        foreach ([1, 2, 3] as $priority) {
            if (!isset($priorityGroups[$priority]) || empty($priorityGroups[$priority])) {
                continue;
            }

            $timeForPriority = $this->getTimeForPriority(
                $priority,
                $availableMinutes,
                $usedMinutes,
                $priorityGroups
            );

            if ($timeForPriority <= 0) {
                continue;
            }

            $usedMinutes += $this->distributeTime(
                $priorityGroups[$priority],
                $timeForPriority,
                $dayPlan,
                $currentDate,
                $allWorkDays,
                $employee
            );
        }

        // 4. Если осталось время — добиваем просроченные и приоритет 1
        $remainingTime = $availableMinutes - $usedMinutes;
        if ($remainingTime >= self::MIN_TASK_MINUTES) {
            $extraTasks = array_merge(
                $priorityGroups[1] ?? [],
                $priorityGroups[2] ?? []
            );
            
            if (!empty($extraTasks)) {
                $this->distributeTime(
                    $extraTasks,
                    $remainingTime,
                    $dayPlan,
                    $currentDate,
                    $allWorkDays,
                    $employee
                );
            }
        }

        // 5. Расчет загрузки
        $actualUsed = array_sum(array_column($dayPlan['tasks'], 'minutes'));
        if ($totalMinutes > 0) {
            $dayPlan['load_percent'] = (int)round(($actualUsed / $totalMinutes) * 100);
        }

        // 6. Сохраняем инстансы
        $this->saveTaskInstances($dayPlan, $taskData, $dateStr);

        return $dayPlan;
    }

    /**
     * Получить активные задачи на дату
     */
    private function getActiveTasksForDate(array &$taskData, Carbon $date): array
    {
        $active = [];

        foreach ($taskData as $id => &$data) {
            if ($data['is_completed']) continue;
            if ($data['remaining_minutes'] < $data['min_daily_minutes']) continue;
            if ($date->lt($data['task_start']) || $date->gt($data['task_end'])) continue;

            $active[$id] = &$data;
        }

        return $active;
    }

    /**
     * Группировка задач по приоритетам
     */
    private function groupTasksByPriority(array $tasks): array
    {
        $groups = [1 => [], 2 => [], 3 => []];

        foreach ($tasks as $id => &$task) {
            $priority = $task['priority'];
            $groups[$priority][$id] = &$task;
        }

        // Внутри группы сортируем: просроченные → по дедлайну
        foreach ($groups as $priority => &$group) {
            uasort($group, function ($a, $b) {
                if ($a['is_overdue'] && !$b['is_overdue']) return -1;
                if (!$a['is_overdue'] && $b['is_overdue']) return 1;
                return $a['task_end']->timestamp - $b['task_end']->timestamp;
            });
        }

        return $groups;
    }

    /**
     * Расчет времени для приоритета
     */
    private function getTimeForPriority(int $priority, int $totalAvailable, int $used, array $priorityGroups): int
    {
        $remaining = $totalAvailable - $used;
        if ($remaining <= 0) return 0;

        switch ($priority) {
            case 1:
                // Если есть задачи приоритета 1 — они получают всё или почти всё
                return $remaining;

            case 2:
                // Если есть приоритет 1, приоритет 2 получает остатки
                // Если нет приоритета 1, получает 80%
                $hasPriority1 = !empty($priorityGroups[1]);
                return $hasPriority1 ? (int)($remaining * 0.3) : (int)($remaining * 0.8);

            case 3:
                // Низкий приоритет — только если есть свободное время
                $hasHigherPriority = !empty($priorityGroups[1]) || !empty($priorityGroups[2]);
                return $hasHigherPriority ? (int)($remaining * 0.1) : $remaining;

            default:
                return 0;
        }
    }

    /**
     * Распределение времени между задачами
     */
    private function distributeTime(
        array &$tasks,
        int $availableTime,
        array &$dayPlan,
        Carbon $currentDate,
        $allWorkDays,
        Employee $employee
    ): int {
        if (empty($tasks) || $availableTime < self::MIN_TASK_MINUTES) {
            return 0;
        }

        $usedTime = 0;
        $remainingToDistribute = $availableTime;

        // Если одна задача — отдаем всё
        if (count($tasks) === 1) {
            $task = &$tasks[array_key_first($tasks)];
            return $this->allocateTimeToTask($task, $availableTime, $dayPlan, $currentDate);
        }

        // Сначала распределяем минимально необходимое время (min_daily_minutes)
        foreach ($tasks as $id => &$task) {
            $minNeeded = $task['min_daily_minutes'];
            
            if ($remainingToDistribute < $minNeeded) {
                continue; // Не хватает времени даже на минимум — пропускаем
            }

            $allocated = min($minNeeded, $task['remaining_minutes'], $remainingToDistribute);
            $allocated = $this->roundToIteration($allocated, $task['iteration_time']);

            if ($allocated > 0) {
                $used = $this->allocateTimeToTask($task, $allocated, $dayPlan, $currentDate);
                $usedTime += $used;
                $remainingToDistribute -= $used;
            }
        }

        // Затем распределяем остаток пропорционально
        if ($remainingToDistribute >= self::MIN_TASK_MINUTES) {
            // Считаем общую оставшуюся потребность
            $totalRemaining = 0;
            foreach ($tasks as &$task) {
                $remaining = $task['remaining_minutes'];
                if ($remaining > 0) {
                    $totalRemaining += $remaining;
                }
            }

            if ($totalRemaining > 0) {
                foreach ($tasks as $id => &$task) {
                    if ($remainingToDistribute < self::MIN_TASK_MINUTES) break;
                    if ($task['remaining_minutes'] <= 0) continue;

                    $proportion = $task['remaining_minutes'] / $totalRemaining;
                    $extraTime = (int)($remainingToDistribute * $proportion);
                    $extraTime = $this->roundToIteration($extraTime, $task['iteration_time']);
                    $extraTime = min($extraTime, $task['remaining_minutes'], $remainingToDistribute);

                    if ($extraTime > 0) {
                        $used = $this->allocateTimeToTask($task, $extraTime, $dayPlan, $currentDate, true);
                        $usedTime += $used;
                        $remainingToDistribute -= $used;
                    }
                }
            }
        }

        // Если осталось время — отдаем первой задаче (самой приоритетной)
        if ($remainingToDistribute >= self::MIN_TASK_MINUTES && !empty($tasks)) {
            $firstTask = &$tasks[array_key_first($tasks)];
            $extraTime = $this->roundToIteration($remainingToDistribute, $firstTask['iteration_time']);
            $extraTime = min($extraTime, $firstTask['remaining_minutes']);

            if ($extraTime > 0) {
                $used = $this->allocateTimeToTask($firstTask, $extraTime, $dayPlan, $currentDate, true);
                $usedTime += $used;
            }
        }

        return $usedTime;
    }

    /**
     * Выделить время на задачу
     */
    private function allocateTimeToTask(
        array &$task,
        int $minutes,
        array &$dayPlan,
        Carbon $currentDate,
        bool $isExtra = false
    ): int {
        // Округляем до целых итераций
        $minutes = $this->roundToIteration($minutes, $task['iteration_time']);
        $minutes = min($minutes, $task['remaining_minutes']);

        if ($minutes <= 0) {
            return 0;
        }

        $taskId = $task['task_id'];
        $iterationTime = $task['iteration_time'];

        // Добавляем в план
        if (!isset($dayPlan['tasks'][$taskId])) {
            $dayPlan['tasks'][$taskId] = [
                'minutes' => 0,
                'assignment_id' => $task['assignment_id'],
            ];
        }
        $dayPlan['tasks'][$taskId]['minutes'] += $minutes;

        // Обновляем остатки
        $task['remaining_minutes'] -= $minutes;

        // Уменьшаем пропущенную квоту
        $iterationsDone = (int)($minutes / $iterationTime);
        $task['missed_quota'] = max(0, ($task['missed_quota'] ?? 0) - $iterationsDone);
        if ($task['missed_quota'] <= 0) {
            $task['is_overdue'] = false;
        }

        // Мета-данные
        $dailyQuota = (int)round($minutes / $iterationTime);
        $dayPlan['task_meta'][$taskId] = [
            'minutes' => $minutes,
            'daily_quota' => $dailyQuota,
            'task_name' => $task['task_name'],
            'task_name_short' => $task['task_name_short'],
            'task_total_minutes' => $task['total_quota'] * $iterationTime,
            'remaining_minutes' => $task['remaining_minutes'],
            'quota_total' => $task['total_quota'],
            'completed_count' => $task['completed_count'],
            'remaining_quota' => max(0, (int)ceil($task['remaining_minutes'] / $iterationTime)),
            'adjusted_remaining_quota' => $task['adjusted_remaining_quota'],
            'start_date' => $task['task_start']->format('d.m.Y'),
            'end_date' => $task['task_end']->format('d.m.Y'),
            'task_id' => $taskId,
            'iteration_time' => $iterationTime,
            'priority' => $task['priority'],
            'is_overdue' => $task['is_overdue'],
            'missed_quota' => $task['missed_quota'] ?? 0,
            'remaining_work_days' => $task['remaining_work_days'],
            'is_extra' => $isExtra,
        ];

        return $minutes;
    }

    /**
     * Округление до целых итераций
     */
    private function roundToIteration(int $minutes, int $iterationTime): int
    {
        if ($iterationTime <= 0) return $minutes;

        $iterations = (int)($minutes / $iterationTime);
        return $iterations * $iterationTime;
    }

    // ... остальные методы (saveTaskInstances, cleanupOldInstances, generateSummary, truncateText) ...
    
    private function saveTaskInstances(array $dayPlan, array $taskData, string $dateStr): void
    {
        foreach ($dayPlan['tasks'] as $taskId => $data) {
            $iterationTime = 60;
            foreach ($taskData as $tData) {
                if ($tData['task_id'] === $taskId) {
                    $iterationTime = $tData['iteration_time'];
                    break;
                }
            }

            $dailyQuota = (int)round($data['minutes'] / $iterationTime);

            if ($dailyQuota > 0) {
                TaskInstance::updateOrCreate(
                    ['task_id' => $taskId, 'date' => $dateStr],
                    ['daily_quota' => $dailyQuota]
                );
            }
        }
    }

    private function cleanupOldInstances(array $taskData, Carbon $today): void
    {
        $taskIds = array_column($taskData, 'task_id');
        TaskInstance::whereIn('task_id', $taskIds)
            ->where('date', '<', $today->format('Y-m-d'))
            ->delete();
    }

    private function generateSummary(array $taskData, Carbon $today): array
    {
        $summary = [
            'total_tasks' => count($taskData),
            'overdue_tasks' => 0,
            'completed_tasks' => 0,
            'total_remaining_minutes' => 0,
        ];

        foreach ($taskData as $data) {
            if ($data['is_completed']) {
                $summary['completed_tasks']++;
            } else {
                $summary['total_remaining_minutes'] += $data['remaining_minutes'];
                if ($data['is_overdue']) {
                    $summary['overdue_tasks']++;
                }
            }
        }

        return $summary;
    }

    private function truncateText($text, $length): string
    {
        if (mb_strlen($text) <= $length) return $text;
        return mb_substr($text, 0, $length) . '...';
    }

    // Методы redistributeForEmployee и redistributeAfterCompletion остаются без изменений...
    public function redistributeForEmployee(Employee $employee, Carbon $from): void
    {
        // ... существующий код ...
    }

    public function redistributeAfterCompletion(TaskAssignment $assignment): void
    {
        // ... существующий код ...
    }
}