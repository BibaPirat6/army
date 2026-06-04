<?php

namespace App\Services\Schedule;

use Carbon\CarbonImmutable;

class TimelineBuilder
{
    protected int $pixelsPerMinute = 2;

    public function build(array $dayData): array
    {
        if (!$dayData || !isset($dayData['work_day'])) {
            return [];
        }

        $workDay = $dayData['work_day'];
        $workStart = CarbonImmutable::parse($workDay->work_start);
        $workEnd = CarbonImmutable::parse($workDay->work_end);
        $workDuration = $workStart->diffInMinutes($workEnd);

        $blocks = [];
        $current = $workStart;

        $breaks = collect($workDay->breaks ?? [])
            ->sortBy('start')
            ->values();

        $tasks = collect($dayData['tasks'] ?? [])
            ->map(function ($task, $taskId) use ($dayData) {
                $meta = $dayData['task_meta'][$taskId] ?? [];
                return [
                    'task_id' => $taskId,
                    'minutes' => $task['minutes'],
                    'assignment_id' => $task['assignment_id'],
                    'priority' => $meta['priority'] ?? 999,
                    'title' => $meta['task_name'] ?? 'Без названия',
                    'color' => $meta['color'] ?? '#3B82F6',
                ];
            })
            ->sortBy('priority')
            ->values();

        foreach ($tasks as $task) {
            $remainingMinutes = $task['minutes'];

            while ($remainingMinutes > 0) {
                $activeBreak = $this->findBreakInside($current, $current->addMinutes($remainingMinutes), $breaks);

                if (!$activeBreak) {
                    $end = $current->addMinutes($remainingMinutes);
                    $blocks[] = $this->makeTaskBlock($task, $current, $end, $workStart);
                    $current = $end;
                    break;
                }

                $breakStart = CarbonImmutable::parse($activeBreak['start']);
                $minutesBeforeBreak = $current->diffInMinutes($breakStart);

                if ($minutesBeforeBreak > 0) {
                    $beforeBreakEnd = $current->addMinutes($minutesBeforeBreak);
                    $blocks[] = $this->makeTaskBlock($task, $current, $beforeBreakEnd, $workStart);
                    $remainingMinutes -= $minutesBeforeBreak;
                    $current = $beforeBreakEnd;
                }

                $breakEnd = CarbonImmutable::parse($activeBreak['end']);
                $blocks[] = $this->makeBreakBlock($breakStart, $breakEnd, $workStart, $activeBreak['type'] ?? 'break');
                $current = $breakEnd;
            }
        }

        return [
            'blocks' => $blocks,
            'work_start' => $workStart,
            'work_end' => $workEnd,
            'work_duration' => $workDuration,
        ];
    }

    protected function findBreakInside(CarbonImmutable $start, CarbonImmutable $end, $breaks): ?array
    {
        foreach ($breaks as $break) {
            $breakStart = CarbonImmutable::parse($break['start']);
            if ($breakStart->between($start, $end)) {
                return $break;
            }
        }
        return null;
    }

    protected function makeTaskBlock(array $task, CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $workStart): array
    {
        $duration = $start->diffInMinutes($end);
        $offsetFromStart = $workStart->diffInMinutes($start);

        return [
            'type' => 'task',
            'title' => $task['title'],
            'task_id' => $task['task_id'],
            'assignment_id' => $task['assignment_id'],
            'color' => $task['color'],
            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),
            'left_percent' => ($offsetFromStart / $this->getTotalMinutes($workStart, $end)) * 100,
            'width_minutes' => $duration,
            'duration' => $duration,
        ];
    }

    protected function makeBreakBlock(CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $workStart, string $type): array
    {
        $duration = $start->diffInMinutes($end);
        $offsetFromStart = $workStart->diffInMinutes($start);

        return [
            'type' => 'break',
            'break_type' => $type,
            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),
            'left_percent' => ($offsetFromStart / $this->getTotalMinutes($workStart, $end)) * 100,
            'width_minutes' => $duration,
            'duration' => $duration,
        ];
    }

    protected function getTotalMinutes(CarbonImmutable $workStart, CarbonImmutable $workEnd): int
    {
        return max($workStart->diffInMinutes($workEnd), 1);
    }
}