<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\EmployeePosition;
use App\Models\Task;
use App\Services\TaskStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(TaskStatsService $statsService)
    {
        $employeePositions = $this->getChiefPositions();
        $taskStats = $statsService->getStats();

        return view('admin.calendar.index', compact('employeePositions', 'taskStats'));
    }

    private function getChiefPositions()
    {
        return EmployeePosition::with([
            'employee.person',
            'commissariatPosition.position.chiefType',
            'commissariatPosition.commissariat',
            'commissariatPosition.department',
            'commissariatPosition.division',
        ])
            ->whereHas('commissariatPosition.position.chiefType', function ($query) {
                $query->whereIn('id', [2, 3, 4]);
            })
            ->get();
    }

    public function stats(TaskStatsService $statsService)
    {
        return response()->json($statsService->getStats());
    }

    public function events(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $tasks = Task::where(function ($query) use ($start, $end) {
            $query->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                });
        })->get();

        $events = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->start_date->toDateString(),
                'end' => $task->end_date
                    ? Carbon::parse($task->end_date)->addDay()->toDateString()
                    : Carbon::parse($task->start_date)->addDay()->toDateString(),
                'color' => $task->color,
                'extendedProps' => [
                    'description' => $task->description,
                    'quota' => $task->quota,
                    'employee_position_id' => $task->employee_position_id,
                ],
            ];
        });

        return response()->json($events);
    }
}