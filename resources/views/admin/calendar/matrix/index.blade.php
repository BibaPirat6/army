@extends('layouts.main')

@section('header-title')
    Матрица сотрудников — {{ $unitName }}
@endsection

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Хлебные крошки --}}
    <nav class="flex mb-4 text-sm text-gray-500">
        @foreach($breadcrumbs as $crumb)
            @if(!$loop->last)
                <a href="{{ $crumb['url'] ?? '#' }}" class="hover:text-indigo-600">{{ $crumb['name'] }}</a>
                <span class="mx-2">/</span>
            @else
                <span class="font-medium text-gray-700">{{ $crumb['name'] }}</span>
            @endif
        @endforeach
    </nav>

    {{-- Заголовок --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $unitName }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                Задач: {{ $totals['tasks'] }} |
                Сотрудников в подразделении: {{ $totals['employees'] }} |
                Назначений: {{ $totals['assignments'] }}
                @if($totals['unassigned'] > 0)
                    | <span class="text-red-500">Неназначенных задач: {{ $totals['unassigned'] }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('calendar.index') }}"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            ← Назад к календарю
        </a>
    </div>

    @if($tasks->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-700">Нет задач в этом подразделении</p>
        </div>
    @else
        {{-- Матрица --}}
        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        {{-- Задача --}}
                        <th class="sticky left-0 z-20 bg-gray-50 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[220px] border-r border-gray-200">
                            Задача
                        </th>

                        {{-- Назначения --}}
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[300px]">
                            Назначенные сотрудники
                        </th>

                        {{-- Прогресс --}}
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[130px]">
                            Прогресс
                        </th>

                        {{-- Действия --}}
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[140px]">
                            Действия
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($matrix as $row)
                        @php
                            $task = $row['task'];
                            $taskAssignments = $row['assignments'];
                            $percent = $row['total_quota'] > 0
                                ? round(($row['total_completed'] / $row['total_quota']) * 100)
                                : 0;
                            $barColor = $percent >= 100 ? 'bg-emerald-500' : ($percent > 50 ? 'bg-indigo-500' : 'bg-amber-500');
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            {{-- Задача --}}
                            <td class="sticky left-0 z-10 bg-white px-4 py-3 border-r border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $task->color }}"></span>
                                    <div>
                                        <a href="{{ route('calendar.show', $task->id) }}"
                                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 hover:underline transition">
                                            {{ $task->title }}
                                        </a>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $task->start_date->format('d.m.Y') }}
                                            @if($task->end_date)
                                                — {{ $task->end_date->format('d.m.Y') }}
                                            @endif
                                            | Квота: {{ $task->quota ?? 'не задана' }}
                                        </div>
                                        @if($task->subtasks->isNotEmpty())
                                            <div class="text-xs text-gray-400">
                                                ⏱ {{ $task->total_avg_time }} мин/итерация
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Назначенные сотрудники --}}
                            <td class="px-4 py-3">
                                @if($taskAssignments->isEmpty())
                                    <span class="text-sm text-red-400 italic">Нет назначенных сотрудников</span>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($taskAssignments as $assignment)
                                            @php
                                                $person = $assignment->employee->person;
                                                $empName = $person
                                                    ? trim($person->фамилия . ' ' . mb_substr($person->имя, 0, 1) . '.' . ($person->отчество ? ' ' . mb_substr($person->отчество, 0, 1) . '.' : ''))
                                                    : 'Сотрудник #' . $assignment->employee->id;
                                                $empPercent = $assignment->quota > 0
                                                    ? round(($assignment->completed_count / $assignment->quota) * 100)
                                                    : 0;
                                            @endphp
                                            <a href="{{ route('employees.show', $assignment->employee_id) }}"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition text-sm"
                                                title="Приоритет: {{ $assignment->priority }} | {{ $assignment->completed_count }}/{{ $assignment->quota }}">
                                                <span class="font-medium text-gray-700">{{ $empName }}</span>
                                                <span class="text-xs {{ $empPercent >= 100 ? 'text-emerald-600' : 'text-gray-400' }}">
                                                    {{ $empPercent }}%
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            {{-- Прогресс --}}
                            <td class="px-4 py-3 text-center">
                                @if($row['total_quota'] > 0)
                                    <div class="text-sm font-semibold {{ $percent >= 100 ? 'text-emerald-600' : ($percent > 50 ? 'text-indigo-600' : 'text-amber-600') }}">
                                        {{ $percent }}%
                                    </div>
                                    <div class="mt-1.5 w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $barColor }} transition-all duration-300"
                                            style="width: {{ $percent }}%">
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        {{ $row['total_completed'] }} / {{ $row['total_quota'] }}
                                    </div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Действия --}}
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('calendar.show', $task->id) }}#assign"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Назначить
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Легенда --}}
        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Завершено (100%)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-indigo-500"></span> В работе (51–99%)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span> Начато (1–50%)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="text-red-400 italic text-sm">Нет сотрудников</span> — требуется назначение
            </span>
        </div>
    @endif
</div>
@endsection