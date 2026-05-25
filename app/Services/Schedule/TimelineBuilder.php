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

        $blocks = [];

        $current = CarbonImmutable::parse($workDay->work_start);

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

                $activeBreak = $this->findBreakInside(
                    $current,
                    $current->addMinutes($remainingMinutes),
                    $breaks
                );

                if (!$activeBreak) {

                    $end = $current->addMinutes($remainingMinutes);

                    $blocks[] = $this->makeTaskBlock(
                        $task,
                        $current,
                        $end
                    );

                    $current = $end;

                    break;
                }

                $breakStart = CarbonImmutable::parse($activeBreak['start']);

                $minutesBeforeBreak = $current->diffInMinutes($breakStart);

                if ($minutesBeforeBreak > 0) {

                    $beforeBreakEnd = $current->addMinutes($minutesBeforeBreak);

                    $blocks[] = $this->makeTaskBlock(
                        $task,
                        $current,
                        $beforeBreakEnd
                    );

                    $remainingMinutes -= $minutesBeforeBreak;

                    $current = $beforeBreakEnd;
                }

                $breakEnd = CarbonImmutable::parse($activeBreak['end']);

                $blocks[] = $this->makeBreakBlock(
                    $breakStart,
                    $breakEnd,
                    $activeBreak['type'] ?? 'break'
                );

                $current = $breakEnd;
            }
        }

        return $blocks;
    }

    protected function findBreakInside(
        CarbonImmutable $start,
        CarbonImmutable $end,
        $breaks
    ): ?array {
        foreach ($breaks as $break) {

            $breakStart = CarbonImmutable::parse($break['start']);

            if ($breakStart->between($start, $end)) {
                return $break;
            }
        }

        return null;
    }

    protected function makeTaskBlock(
        array $task,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $duration = $start->diffInMinutes($end);

        return [
            'type' => 'task',

            'title' => $task['title'],
            'task_id' => $task['task_id'],
            'assignment_id' => $task['assignment_id'],

            'color' => $task['color'],

            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),

            'top' => $this->minutesFromMidnight($start) * $this->pixelsPerMinute,

            'height' => max(
                $duration * $this->pixelsPerMinute,
                32
            ),
        ];
    }

    protected function makeBreakBlock(
        CarbonImmutable $start,
        CarbonImmutable $end,
        string $type
    ): array {
        $duration = $start->diffInMinutes($end);

        return [
            'type' => 'break',

            'break_type' => $type,

            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),

            'top' => $this->minutesFromMidnight($start) * $this->pixelsPerMinute,

            'height' => $duration * $this->pixelsPerMinute,
        ];
    }

    protected function minutesFromMidnight(
        CarbonImmutable $time
    ): int {
        return ($time->hour * 60) + $time->minute;
    }
}