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



      /**
     * Обновить выполнение задачи и перераспределить нагрузку
     */
    public function updateTaskProgress(TaskAssignment $assignment, int $completedCount, ?int $newQuota = null): void
    {
        // Обновляем выполнение
        $assignment->update([
            'completed_count' => $completedCount
        ]);
        
        // Если изменили квоту - обновляем и её
        if ($newQuota !== null && $newQuota !== $assignment->quota) {
            $assignment->update([
                'quota' => $newQuota
            ]);
        }
        
        // Удаляем будущие распределения
        TaskInstance::where('task_id', $assignment->task_id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->delete();
        
        // Если осталась квота - перераспределяем
        if ($assignment->quota > $assignment->completed_count) {
            $this->redistributeForEmployee($assignment->employee, now());
        }
    }
    
    /**
     * Перераспределить нагрузку для сотрудника
     */
    private function redistributeForEmployee(Employee $employee, Carbon $from): void
    {
        $to = $from->copy()->addMonths(3); // Или ваш период
        
        // Получаем все рабочие дни
        $workDays = WorkDay::where('employee_id', $employee->id)
            ->where('date', '>=', $from->format('Y-m-d'))
            ->where('date', '<=', $to->format('Y-m-d'))
            ->orderBy('date')
            ->get();
            
        if ($workDays->isEmpty()) return;
        
        // Получаем активные назначения
        $assignments = TaskAssignment::with('task.subtasks')
            ->where('employee_id', $employee->id)
            ->where('completed_count', '<', \DB::raw('quota'))
            ->get()
            ->sortBy('priority');
            
        $remainingTime = [];
        foreach ($assignments as $a) {
            $iterationTime = $a->task->subtasks->sum('avg_time_minutes') ?: 60;
            $remaining = ($a->quota - $a->completed_count) * $iterationTime;
            $remainingTime[$a->id] = $remaining;
        }
        
        // Простое пропорциональное распределение
        foreach ($workDays as $day) {
            if ($day->type !== 'рабочий_день' || !$day->total_minutes) continue;
            
            $available = (int) $day->total_minutes;
            $used = 0;
            
            foreach ($assignments as $assignment) {
                if (!isset($remainingTime[$assignment->id]) || $remainingTime[$assignment->id] <= 0) continue;
                
                $left = $available - $used;
                if ($left <= 0) break;
                
                $alloc = min($remainingTime[$assignment->id], $left);
                $alloc = (int) floor($alloc);
                
                if ($alloc <= 0) continue;
                
                $iterationTime = $assignment->task->subtasks->sum('avg_time_minutes') ?: 60;
                $quota = (int) round($alloc / $iterationTime);
                
                TaskInstance::updateOrCreate(
                    ['task_id' => $assignment->task_id, 'date' => $day->date->format('Y-m-d')],
                    ['daily_quota' => $quota]
                );
                
                $remainingTime[$assignment->id] -= $alloc;
                $used += $alloc;
            }
        }
    }


     /**
     * Перераспределить после отметки выполнения
     */
    public function redistributeAfterCompletion(TaskAssignment $assignment): void
    {
        // Удаляем будущие распределения
        TaskInstance::where('task_id', $assignment->task_id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->delete();
        
        // Считаем оставшееся время
        $iterationTime = $assignment->task->subtasks->sum('avg_time_minutes') ?: 60;
        $remainingQuota = $assignment->quota - $assignment->completed_count;
        $remainingMinutes = $remainingQuota * $iterationTime;
        
        if ($remainingMinutes <= 0) return;
        
        // Получаем будущие рабочие дни
        $workDays = WorkDay::where('employee_id', $assignment->employee_id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->where('type', 'рабочий_день')
            ->whereNotNull('work_start')
            ->orderBy('date')
            ->get();
        
        if ($workDays->isEmpty()) return;
        
        // Простое распределение: раскидываем минуты по дням
        $totalAvailableMinutes = $workDays->sum('total_minutes');
        
        foreach ($workDays as $day) {
            if ($remainingMinutes <= 0) break;
            
            $dayMinutes = (int) $day->total_minutes;
            if ($dayMinutes <= 0) continue;
            
            // Выделяем пропорциональную часть
            $alloc = min($remainingMinutes, $dayMinutes);
            $quota = (int) ceil($alloc / $iterationTime);
            
            TaskInstance::updateOrCreate(
                ['task_id' => $assignment->task_id, 'date' => $day->date->format('Y-m-d')],
                ['daily_quota' => $quota]
            );
            
            $remainingMinutes -= $alloc;
        }
    }
}