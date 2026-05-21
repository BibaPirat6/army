<?php


namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TaskAssignment;
use App\Models\WorkDay;
use App\Services\WorkloadPlanner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
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
            'year' => 'required|integer|min:2020|max:2100',
            'weekly_hours' => 'required|integer|min:1|max:168',
            'days' => 'required|array',
            'days.*.type' => 'required|in:рабочий_день,выходной',
            'days.*.work_start' => 'nullable|date_format:H:i',
            'days.*.work_end' => 'nullable|date_format:H:i',
            'days.*.breaks' => 'nullable|array',
            'days.*.breaks.*.start' => 'nullable|date_format:H:i',
            'days.*.breaks.*.end' => 'nullable|date_format:H:i',
        ]);

        $year = (int) $validated['year'];
        $weeklyHours = (int) $validated['weekly_hours'];

        $start = Carbon::create($year, 1, 1);
        $end = Carbon::create($year, 12, 31);

        $current = $start->copy();
        while ($current->lte($end)) {
            $dow = (string) $current->dayOfWeek;
            $dayConfig = $validated['days'][$dow] ?? null;
            $isWorking = $dayConfig && ($dayConfig['type'] === 'рабочий_день');

            $breaks = [];
            if ($isWorking && ! empty($dayConfig['breaks'])) {
                foreach ($dayConfig['breaks'] as $b) {
                    if (! empty($b['start']) && ! empty($b['end'])) {
                        $breaks[] = ['start' => $b['start'], 'end' => $b['end']];
                    }
                }
            }

            WorkDay::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $current->toDateString()],
                [
                    'type' => $isWorking ? 'рабочий_день' : 'выходной',
                    'work_start' => $isWorking ? ($dayConfig['work_start'] ?? '09:00') : null,
                    'work_end' => $isWorking ? ($dayConfig['work_end'] ?? '18:00') : null,
                    'breaks' => $isWorking ? $breaks : null,
                    'weekly_hours' => $weeklyHours,
                ]
            );

            $current->addDay();
        }

        return redirect()->route('calendar.schedule.employee', $employee->id)
            ->with('success', 'График сохранён');
    }

    private function calcDayTarget(array $config): int
    {
        [$sh, $sm] = explode(':', $config['work_start'] ?? '09:00');
        [$eh, $em] = explode(':', $config['work_end'] ?? '18:00');
        $total = ($eh * 60 + $em) - ($sh * 60 + $sm);
        foreach ($config['breaks'] ?? [] as $b) {
            if (! empty($b['start']) && ! empty($b['end'])) {
                [$bsh, $bsm] = explode(':', $b['start']);
                [$beh, $bem] = explode(':', $b['end']);
                $total -= ($beh * 60 + $bem) - ($bsh * 60 + $bsm);
            }
        }

        return max(0, (int) round($total / 60));
    }

    public function index(Request $request, Employee $employee)
{
    $month = (int) $request->input('month', now()->month);
    $year = (int) $request->input('year', now()->year);

    $from = Carbon::create($year, $month, 1)->startOfMonth();
    $to = $from->copy()->endOfMonth();

    $days = [];

    $current = $from->copy();

    while ($current->lte($to)) {

        $days[] = [
            'date' => $current->copy(),
        ];

        $current->addDay();
    }

    return view(
        'admin.calendar.schedule.index',
        compact(
            'employee',
            'month',
            'year',
            'days'
        )
    );
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
