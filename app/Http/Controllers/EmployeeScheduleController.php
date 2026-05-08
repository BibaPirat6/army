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
    /**
     * Форма настройки графика.
     */
    public function setup(Request $request, Employee $employee)
    {
        return view('admin.calendar.schedule.setup', compact('employee'));
    }

    /**
     * Сохранение графика.
     */
    public function generate(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'year'         => 'required|integer|min:2020|max:2100',
            'work_start'   => 'required|date_format:H:i',
            'work_end'     => 'required|date_format:H:i',
            'break_start'  => 'required|date_format:H:i',
            'break_end'    => 'required|date_format:H:i',
            'weekly_hours' => 'required|integer|min:1|max:168',
            'work_days'    => 'required|array|min:1',
            'work_days.*'  => 'integer|min:0|max:6', // 0 вс, 1 пн, …, 6 сб
        ]);

        $year       = (int) $validated['year'];
        $workStart  = $validated['work_start'];
        $workEnd    = $validated['work_end'];
        $breakStart = $validated['break_start'];
        $breakEnd   = $validated['break_end'];
        $weeklyHours = (int) $validated['weekly_hours'];
        $workDays   = array_map('intval', $validated['work_days']);
        $breaks     = [['start' => $breakStart, 'end' => $breakEnd]];

        $start = Carbon::create($year, 1, 1);
        $end   = Carbon::create($year, 12, 31);

        $current = $start->copy();
        while ($current->lte($end)) {
            $isWorking = in_array($current->dayOfWeek, $workDays);

            WorkDay::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $current->toDateString()],
                [
                    'type'         => $isWorking ? 'рабочий_день' : 'выходной',
                    'work_start'   => $isWorking ? $workStart : null,
                    'work_end'     => $isWorking ? $workEnd : null,
                    'breaks'       => $isWorking ? $breaks : null,
                    'weekly_hours' => $weeklyHours,
                ]
            );

            $current->addDay();
        }

        return redirect()->route('calendar.schedule.employee', $employee->id)
            ->with('success', 'График на ' . $year . ' год создан');
    }

    /**
     * Недельное расписание.
     */
    public function index(Request $request, Employee $employee, WorkloadPlanner $planner)
    {
        $week = $request->input('week', now()->weekOfYear);
        $year = $request->input('year', now()->year);
        $from = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $to   = $from->copy()->endOfWeek();

        $schedule = $planner->generatePlan($employee, $from, $to);

        return view('admin.calendar.schedule.index', compact('employee', 'schedule', 'week', 'year', 'from', 'to'));
    }

    /**
     * Отметка выполнения итерации.
     */
    public function complete(Request $request, TaskAssignment $taskAssignment)
    {
        $amount = (int) $request->input('amount', 1);
        $taskAssignment->increment('completed_count', $amount);

        return back()->with('success', "+{$amount} выполнено");
    }
}