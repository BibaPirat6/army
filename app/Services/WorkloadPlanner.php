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
     * Генерирует дневной план для сотрудника.
     */
    public function generatePlan(Employee $employee, Carbon $from, Carbon $to): array
    {
        $workDays = WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->keyBy(fn($d) => $d->date->toDateString());

        $assignments = TaskAssignment::with('task.subtasks')
            ->where('employee_id', $employee->id)
            ->where(function ($q) use ($from, $to) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $to);
                $q->whereNull('end_date')->orWhere('end_date', '>=', $from);
            })
            ->get()
            ->sortBy('priority');

        // Оставшаяся работа в минутах
        $remaining = [];
        foreach ($assignments as $a) {
            $remaining[$a->id] = max(0, $a->task->total_avg_time * ($a->quota - $a->completed_count));
        }

        $plan = [];
        $current = $from->copy();

        while ($current->lte($to)) {
            $dateStr = $current->toDateString();
            $wd = $workDays->get($dateStr);
            $dayPlan = [];

            if ($wd && in_array($wd->type, ['рабочий_день']) && $wd->work_start) {
                $available = $wd->total_minutes;

                foreach ($assignments as $a) {
                    if (($remaining[$a->id] ?? 0) <= 0) continue;
                    $alloc = min($remaining[$a->id], $available);
                    if ($alloc <= 0) break;

                    $dayPlan[$a->task_id] = $alloc;
                    $remaining[$a->id] -= $alloc;
                    $available -= $alloc;

                    // Создаём/обновляем task_instance
                    $dailyQuota = (int) round($alloc / max(1, $a->task->total_avg_time));
                    TaskInstance::updateOrCreate(
                        ['task_id' => $a->task_id, 'date' => $dateStr],
                        ['daily_quota' => $dailyQuota]
                    );
                }
            }

            $plan[$dateStr] = ['work_day' => $wd, 'tasks' => $dayPlan];
            $current->addDay();
        }

        return ['plan' => $plan, 'remaining' => $remaining, 'employee' => $employee];
    }

    /**
     * Пересчёт task_instances при изменении конкретного дня.
     */
    public function regenerateForDate(WorkDay $workDay): void
    {
        $from = Carbon::parse($workDay->date)->startOfDay();
        $to = $from->copy()->endOfDay();
        $employee = Employee::find($workDay->employee_id);

        $this->generatePlan($employee, $from, $to);
    }
}