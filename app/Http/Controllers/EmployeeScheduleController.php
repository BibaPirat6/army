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
     * График сотрудника на месяц.
     */
    public function index(Request $request, Employee $employee, WorkloadPlanner $planner)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        // Если work_days пустые — генерируем стандартный график (5/2)
        $this->ensureWorkDaysExist($employee, $year);

        $schedule = $planner->generatePlan($employee, $from, $to);

        return view('admin.calendar.schedule.index', compact('employee', 'schedule', 'month', 'year'));
    }

    /**
     * Генерация/обновление рабочих дней сотрудника на год.
     */
    public function generate(Request $request, Employee $employee)
    {
        $year = $request->input('year', now()->year);
        $template = $request->input('template', '5/2'); // 5/2, 2/2, сменный

        $this->generateYearSchedule($employee, $year, $template);

        return redirect()->route('calendar.schedule.employee', ['employee' => $employee->id, 'year' => $year])
            ->with('success', "График на {$year} год сгенерирован ({$template})");
    }

    /**
     * Обновление конкретного дня (больничный, отпуск, смена графика).
     */
    public function updateWorkDay(Request $request, WorkDay $workDay)
    {
        $validated = $request->validate([
            'type' => 'required|in:рабочий_день,выходной,больничный,отпуск',
            'work_start' => 'nullable|date_format:H:i',
            'work_end' => 'nullable|date_format:H:i',
            'breaks' => 'nullable|json',
        ]);

        // Если нерабочий день — сбрасываем часы
        if (in_array($validated['type'], ['выходной', 'больничный', 'отпуск'])) {
            $validated['work_start'] = null;
            $validated['work_end'] = null;
            $validated['breaks'] = null;
        }

        $workDay->update($validated);

        // Пересчитываем task_instances для этого сотрудника
        app(WorkloadPlanner::class)->regenerateForDate($workDay);

        return back()->with('success', 'День обновлён');
    }

    /**
     * Отметка выполнения итерации задачи сотрудником.
     */
    public function complete(Request $request, TaskAssignment $taskAssignment)
    {
        $taskAssignment->increment('completed_count');

        return back()->with('success', 'Итерация отмечена');
    }

    private function ensureWorkDaysExist(Employee $employee, int $year): void
    {
        $exists = WorkDay::where('employee_id', $employee->id)->whereYear('date', $year)->exists();
        if (! $exists) {
            $this->generateYearSchedule($employee, $year, '5/2', '09:00', '18:00', [['start' => '13:00', 'end' => '14:00']]);
        }
    }

    private function generateYearSchedule(
        Employee $employee,
        int $year,
        string $template,
        string $workStart,
        string $workEnd,
        array $breaks
    ): void {
        $start = Carbon::create($year, 1, 1);
        $end = Carbon::create($year, 12, 31);
        $week = 0;

        $current = $start->copy();
        while ($current->lte($end)) {
            $week = $current->weekOfYear;
            $isWorkingDay = $this->isWorkingDayByTemplate($current, $template, $week);

            WorkDay::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $current->toDateString()],
                [
                    'type' => $isWorkingDay ? 'рабочий_день' : 'выходной',
                    'work_start' => $isWorkingDay ? $workStart : null,
                    'work_end' => $isWorkingDay ? $workEnd : null,
                    'breaks' => $isWorkingDay ? $breaks : null,
                ]
            );
            $current->addDay();
        }
    }

    private function isWorkingDayByTemplate(Carbon $date, string $template, int $week): bool
    {
        $dow = $date->dayOfWeek;   // 0 вс, 1 пн, …, 6 сб
        $doy = $date->dayOfYear;

        return match ($template) {
            '5/2' => ! in_array($dow, [0, 6]),
            '2/2' => ($doy % 4 == 1 || $doy % 4 == 2),
            '6/1' => $dow != 0,
            '1/3' => $dow == 1,
            'сменный' => ($doy % 3 != 0),
            'неделя_2/2_5/2' => ($week % 2 == 1) ? ($doy % 4 == 1 || $doy % 4 == 2) : (! in_array($dow, [0, 6])),
            default => ! in_array($dow, [0, 6]),
        };
    }
}
