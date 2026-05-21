<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TaskInstance;
use App\Models\WorkDay;
use App\Services\WorkloadPlanner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * ГЛАВНАЯ СТРАНИЦА ГРАФИКА
     */
public function index(Request $request, Employee $employee, WorkloadPlanner $planner)
{
    $month = (int) $request->input('month', now()->month);
    $year = (int) $request->input('year', now()->year);

    $from = Carbon::create($year, $month, 1)->startOfMonth();
    $to = $from->copy()->endOfMonth();

    $schedule = $planner->generatePlan($employee, $from, $to);

    return view('admin.calendar.schedule.index', [
        'employee' => $employee,
        'month' => $month,
        'year' => $year,
        'schedule' => $schedule,
        'hasSchedule' => WorkDay::where('employee_id', $employee->id)->exists(),
    ]);
}
    /**
     * ФОРМА НАСТРОЙКИ
     */
    public function setup(Employee $employee)
    {
        return view('admin.calendar.schedule.setup', compact('employee'));
    }

    /**
     * СОХРАНЕНИЕ ГРАФИКА
     */
    public function generate(Request $request, Employee $employee, WorkloadPlanner $planner)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'weekly_hours' => 'required|integer|min:1|max:168',
            'days' => 'required|array',
            'days.*.type' => 'required|in:рабочий_день,выходной',
            'days.*.work_start' => 'nullable|date_format:H:i',
            'days.*.work_end' => 'nullable|date_format:H:i',
            'days.*.breaks' => 'nullable|array',
        ]);

        $year = (int) $validated['year'];

        $start = Carbon::create($year, 1, 1);
        $end = Carbon::create($year, 12, 31);

        $current = $start->copy();

        // 1. СОХРАНЯЕМ work_days
        while ($current->lte($end)) {

            $dow = (string) $current->dayOfWeek;
            $day = $validated['days'][$dow] ?? null;

            $isWorking = $day && $day['type'] === 'рабочий_день';

            WorkDay::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $current->toDateString(),
                ],
                [
                    'type' => $isWorking ? 'рабочий_день' : 'выходной',
                    'work_start' => $isWorking ? ($day['work_start'] ?? '09:00') : null,
                    'work_end' => $isWorking ? ($day['work_end'] ?? '18:00') : null,
                    'breaks' => $isWorking ? ($day['breaks'] ?? null) : null,
                    'weekly_hours' => $validated['weekly_hours'],
                ]
            );

            $current->addDay();
        }

        // 2. ПЕРЕСЧЁТ ПЛАНА (ВАЖНО)
        TaskInstance::whereIn('task_id', function ($q) use ($employee) {
            $q->select('task_id')
                ->from('task_assignments')
                ->where('employee_id', $employee->id);
        })->delete();

        $from = Carbon::create($year, 1, 1);
        $to = Carbon::create($year, 12, 31);

        $planner->generatePlan($employee, $from, $to);

        return redirect()
            ->route('calendar.schedule.employee', $employee->id)
            ->with('success', 'График обновлён и пересчитан');
    }
}
