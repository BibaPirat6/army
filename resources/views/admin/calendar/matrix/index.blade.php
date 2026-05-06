@extends('layouts.main')

@section('header-title')
    Матрица сотрудников — {{ $commissariat->name }}
@endsection

@section('content')
<div class="max-w-full mx-auto px-4 py-4">
    <h2 class="text-lg font-semibold text-gray-800 mb-1">{{ $commissariat->name }}</h2>
    <p class="text-xs text-gray-500 mb-4">
        Задач: {{ count($tasks) }} | Сотрудников: {{ $employees->count() }}
    </p>

    @if($tasks->isEmpty())
        <p class="text-gray-400 text-sm">Нет задач</p>
    @elseif($employees->isEmpty())
        <p class="text-gray-400 text-sm">Нет сотрудников</p>
    @else
        <div class="overflow-auto bg-white rounded-lg shadow-sm border" style="max-height: 75vh;">
            <table class="min-w-max border-collapse text-xs">
                <thead>
                    <tr>
                        <th class="sticky left-0 top-0 z-30 bg-gray-50 px-3 py-2 text-left font-medium text-gray-600 border-r border-b border-gray-200 min-w-[200px]">
                            Сотрудники
                        </th>
                        @foreach($tasks as $task)
                            <th class="sticky top-0 z-20 px-2 py-2 text-center font-normal border-b border-gray-200 min-w-[90px] group"
                                style="background-color: {{ $task->color }}10;">
                                <a href="{{ route('calendar.show', $task->id) }}" class="flex flex-col items-center gap-0.5 hover:opacity-70 relative" title="{{ $task->title }}">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $task->color }}"></span>
                                    <span class="text-gray-700 truncate max-w-[70px]">{{ Str::limit($task->title, 10) }}</span>
                                    {{-- Ответственный при наведении --}}
                                    @if($task->employeePosition)
                                        @php
                                            $respPerson = $task->employeePosition->employee?->person;
                                            $respName = $respPerson
                                                ? $respPerson->фамилия . ' ' . mb_substr($respPerson->имя, 0, 1) . '.'
                                                : null;
                                        @endphp
                                        @if($respName)
                                            <span class="absolute -bottom-4 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-1.5 py-0.5 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-40 pointer-events-none">
                                                {{ $respName }}
                                            </span>
                                        @endif
                                    @endif
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrix as $row)
                        @php $employee = $row['employee']; @endphp
                        <tr>
                            {{-- Сотрудник --}}
                            <td class="sticky left-0 z-10 bg-white px-3 py-2 border-r border-b border-gray-100 whitespace-nowrap">
                                @php
                                    $person = $employee->person;
                                    $fullName = $person
                                        ? trim($person->фамилия . ' ' . $person->имя . ' ' . ($person->отчество ?? ''))
                                        : 'Сотрудник #' . $employee->id;
                                    $ep = $employee->employeePositions->first();
                                    $cp = $ep?->commissariatPosition;
                                    $posName = $cp?->position?->name ?? '';
                                @endphp
                                <a href="{{ route('employees.show', $employee->id) }}" class="text-gray-800 font-medium hover:text-indigo-600 transition">
                                    {{ $fullName }}
                                </a>
                                @if($posName && $cp)
                                    <a href="{{ route('commissariat-positions.show', array_filter([
                                            'id' => $cp->id,
                                            'back_url' => url()->full(),
                                            'commissariat_id' => $commissariat->id,
                                            'employeeId' => $employee->id,
                                        ])) }}"
                                        class="block text-gray-400 hover:text-indigo-600 transition text-[11px]">
                                        {{ $posName }}
                                    </a>
                                @endif
                            </td>

                            {{-- Ячейки задач --}}
                            @foreach($tasks as $task)
                                @php $a = $row['tasks'][$task->id] ?? null; @endphp
                                <td class="relative px-2 py-2 text-center border-b border-gray-100 group min-w-[90px]">
                                    @if($a)
                                        @php
                                            $pct = $a->quota > 0 ? round(($a->completed_count / $a->quota) * 100) : 0;
                                            $color = $pct >= 100 ? 'text-emerald-600' : ($pct > 50 ? 'text-indigo-600' : 'text-amber-600');
                                            $bar  = $pct >= 100 ? 'bg-emerald-500' : ($pct > 50 ? 'bg-indigo-500' : 'bg-amber-500');
                                        @endphp
                                        <div class="text-[11px] font-semibold {{ $color }}">{{ $a->completed_count }}/{{ $a->quota }}</div>
                                        <div class="mt-0.5 w-full bg-gray-200 rounded-full h-1">
                                            <div class="h-1 rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">P{{ $a->priority }}</div>
                                    @else
                                        <span class="text-gray-300">—</span>
                                        <a href="{{ route('calendar.show', $task->id) }}#assign"
                                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="px-1.5 py-0.5 text-[10px] text-white bg-indigo-500 hover:bg-indigo-600 rounded shadow">
                                                +
                                            </span>
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection