<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\TaskInstance;
use App\Models\WorkDay;
use Carbon\Carbon;

class WorkloadPlanner
{
    public function generatePlan(Employee $employee, Carbon $from, Carbon $to): array
    {
        // 1. Получаем рабочие дни
        $workDays = WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->get()->keyBy(fn($d) => $d->date->toDateString());

        // 2. Получаем назначения с подзадачами (важно: with('task.subtasks'))
        $assignments = TaskAssignment::with(['task.subtasks'])
            ->where('employee_id', $employee->id)
            ->whereHas('task', fn($q) => $q->where('start_date', '<=', $to)->where('end_date', '>=', $from))
            ->get()
            ->sortBy('priority') // Сортируем по приоритету сразу
            ->values();

        // 3. Предварительный расчет: сколько минут осталось по каждой задаче
        // Key: assignment_id, Value: remaining_minutes
        $remainingMinutesMap = [];
        
        // Key: task_id, Value: time_per_one_iteration (sum of subtasks avg)
        $taskIterationTimeMap = [];

        foreach ($assignments as $a) {
            // Считаем время одной итерации (сумма средних времен подзадач)
            // Используем коллекцию, так как мы сделали eager loading
            $iterationTime = $a->task->subtasks->sum('avg_time_minutes');
            
            // Если подзадач нет, время = 0 (или можно поставить дефолт, например 60)
            if ($iterationTime <= 0) $iterationTime = 60; 

            $taskIterationTimeMap[$a->task_id] = $iterationTime;

            $remainingQuota = max(0, $a->quota - $a->completed_count);
            $remainingMinutesMap[$a->id] = $remainingQuota * $iterationTime;
        }

        $plan = [];
        $current = $from->copy();

        while ($current->lte($to)) {
            $dateStr = $current->toDateString();
            $wd = $workDays->get($dateStr);
            
            $dayPlan = [];      // [task_id => ['minutes' => int, 'assignment_id' => int]]
            $taskMeta = [];     // Метаданные для отображения в шаблоне
            $loadPercent = 0;

            // Если рабочий день
            if ($wd && $wd->type === 'рабочий_день' && $wd->work_start) {
                $availableMinutes = (int) ($wd->total_minutes ?? 0);
                $usedMinutes = 0;

                // Проходим по задачам в порядке приоритета
                foreach ($assignments as $a) {
                    // Если время задачи уже вышло или закончилась квота - пропускаем
                    if (($remainingMinutesMap[$a->id] ?? 0) <= 0) {
                        continue;
                    }

                    // Сколько свободного времени осталось в дне
                    $timeLeftInDay = $availableMinutes - $usedMinutes;
                    if ($timeLeftInDay <= 0) break; // День заполнен

                    $iterationTime = $taskIterationTimeMap[$a->task_id];
                    
                    // Сколько минут мы можем выделить этой задаче сегодня
                    // Мы не можем выделить больше, чем осталось по задаче
                    // И не больше, чем осталось в дне
                    $alloc = min($remainingMinutesMap[$a->id], $timeLeftInDay);

                    // Округляем до целых минут
                    $alloc = (int) floor($alloc);
                    
                    if ($alloc <= 0) continue;

                    // Записываем в план дня
                    if (!isset($dayPlan[$a->task_id])) {
                        $dayPlan[$a->task_id] = [
                            'minutes' => 0,
                            'assignment_id' => $a->id
                        ];
                    }
                    $dayPlan[$a->task_id]['minutes'] += $alloc;

                    // Обновляем счетчики
                    $remainingMinutesMap[$a->id] -= $alloc;
                    $usedMinutes += $alloc;

                    // Формируем мету для шаблона (берем актуальные данные)
                    // Важно: task_name берем из title
                    $taskMeta[$a->task_id] = [
                        'minutes' => $dayPlan[$a->task_id]['minutes'],
                        'task_name' => $a->task->title, 
                        'task_total_minutes' => $a->quota * $iterationTime, // Всего нужно было
                        'remaining_minutes' => $remainingMinutesMap[$a->id],
                        'quota_total' => $a->quota,
                        'completed_count' => $a->completed_count,
                        'remaining_quota' => max(0, ceil($remainingMinutesMap[$a->id] / $iterationTime)),
                    ];
                }

                // Считаем загрузку дня
                if ($availableMinutes > 0) {
                    $loadPercent = (int) round(($usedMinutes / $availableMinutes) * 100);
                }
            }

            // Сохраняем TaskInstance (опционально, если нужно хранить историю планов)
            foreach ($dayPlan as $taskId => $data) {
                $iterationTime = $taskIterationTimeMap[$taskId] ?? 1;
                TaskInstance::updateOrCreate(
                    ['task_id' => $taskId, 'date' => $dateStr],
                    ['daily_quota' => (int) round($data['minutes'] / $iterationTime)]
                );
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
            'remaining' => $remainingMinutesMap,
            'employee' => $employee,
        ];
    }
}