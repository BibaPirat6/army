<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\WorkDay;
use App\Services\WorkloadPlanner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeScheduleController extends Controller
{
    public function index(Request $request, Employee $employee, WorkloadPlanner $planner)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $this->ensureWorkDaysExist($employee, $year, $from, $to);

        $schedule = $planner->generatePlan($employee, $from, $to);

        // Собираем недели месяца для формы настройки
        $weeks = $this->getMonthWeeks($from);

        return view('admin.calendar.schedule.index', compact(
            'employee', 'schedule', 'month', 'year', 'weeks'
        ));
    }

    /**
     * Детальная настройка графика на месяц.
     */
    public function generate(Request $request, Employee $employee)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $weeks = $request->input('weeks', []);

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $current = $from->copy();
        while ($current->lte($to)) {
            $weekOfMonth = $current->weekOfMonth;
            $dayOfWeek = $current->dayOfWeek; // 1 пн … 0 вс
            $config = $weeks[$weekOfMonth] ?? $this->getDefaultWeekConfig();

            $isWorking = in_array($dayOfWeek, $config['days'] ?? [1, 2, 3, 4, 5]);

            WorkDay::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $current->toDateString()],
                [
                    'type' => $isWorking ? 'рабочий_день' : 'выходной',
                    'work_start' => $isWorking ? ($config['work_start'] ?? '09:00') : null,
                    'work_end' => $isWorking ? ($config['work_end'] ?? '18:00') : null,
                    'breaks' => $isWorking
                        ? [['start' => $config['break_start'] ?? '13:00', 'end' => $config['break_end'] ?? '14:00']]
                        : null,
                ]
            );

            $current->addDay();
        }

        return redirect()->route('calendar.schedule.employee', [
            'employee' => $employee->id,
            'month' => $month,
            'year' => $year,
        ])->with('success', 'График сформирован');
    }

    // ===== Приватные методы =====

    private function ensureWorkDaysExist(Employee $employee, int $year, Carbon $from, Carbon $to): void
    {
        $exists = WorkDay::where('employee_id', $employee->id)
            ->whereBetween('date', [$from, $to])
            ->exists();

        if (! $exists) {
            $current = $from->copy();
            while ($current->lte($to)) {
                $isWorking = ! in_array($current->dayOfWeek, [0, 6]);
                WorkDay::create([
                    'employee_id' => $employee->id,
                    'date' => $current->toDateString(),
                    'type' => $isWorking ? 'рабочий_день' : 'выходной',
                    'work_start' => $isWorking ? '09:00' : null,
                    'work_end' => $isWorking ? '18:00' : null,
                    'breaks' => $isWorking ? json_encode([['start' => '13:00', 'end' => '14:00']]) : null,
                ]);
                $current->addDay();
            }
        }
    }

    private function getMonthWeeks(Carbon $firstDayOfMonth): array
    {
        $weeks = [];
        $current = $firstDayOfMonth->copy();
        $end = $firstDayOfMonth->copy()->endOfMonth();

        while ($current->lte($end)) {
            $week = $current->weekOfMonth;
            if (! isset($weeks[$week])) {
                $weeks[$week] = [
                    'label' => $week.'-я неделя ('.$current->copy()->startOfWeek()->translatedFormat('d M').' – '.$current->copy()->endOfWeek()->translatedFormat('d M').')',
                    'days' => [1, 2, 3, 4, 5],
                    'work_start' => '09:00',
                    'work_end' => '18:00',
                    'break_start' => '13:00',
                    'break_end' => '14:00',
                ];
            }
            $current->addDay();
        }

        return $weeks;
    }

    private function getDefaultWeekConfig(): array
    {
        return [
            'days' => [1, 2, 3, 4, 5],
            'work_start' => '09:00',
            'work_end' => '18:00',
            'break_start' => '13:00',
            'break_end' => '14:00',
        ];
    }

    /**
     * Отметить выполнение итерации.
     */
    public function completeIteration(Request $request, TaskAssignment $taskAssignment)
    {
        $amount = (int) $request->input('amount', 1);
        $taskAssignment->increment('completed_count', $amount);

        return back()->with('success', "Отмечено +{$amount}. Выполнено: {$taskAssignment->completed_count}/{$taskAssignment->quota}");
    }

    /**
     * Полностью завершить назначение.
     */
    public function completeAssignment(Request $request, TaskAssignment $taskAssignment)
    {
        $remaining = $taskAssignment->quota - $taskAssignment->completed_count;
        $taskAssignment->update(['completed_count' => $taskAssignment->quota]);

        return back()->with('success', "Задача полностью выполнена! Закрыто +{$remaining} итераций.");
    }

    /**
     * Снять назначение (сбросить прогресс).
     */
    public function resetAssignment(Request $request, TaskAssignment $taskAssignment)
    {
        $taskAssignment->update(['completed_count' => 0]);

        return back()->with('success', 'Прогресс сброшен.');
    }
}
