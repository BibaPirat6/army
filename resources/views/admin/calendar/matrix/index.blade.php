@extends('layouts.main')

@section('header-title', 'Матрица — ' . $commissariat->name)

@section('content')
<div class="max-w-full mx-auto px-2 py-2">
    <h2 class="text-base font-semibold text-gray-800">{{ $commissariat->name }}</h2>
    <p class="text-xs text-gray-500 mb-2">Задач: {{ count($tasks) }} | Сотрудников: {{ count($matrix) }}</p>

    @if($tasks->isEmpty() || empty($matrix))
        <p class="text-gray-400 text-sm">Нет данных</p>
    @else
        <div class="overflow-auto bg-white rounded shadow border" style="max-height: 80vh; max-width: 100%;">
            <table class="border-collapse text-xs">
                <thead>
                    <tr>
                        <th class="sticky left-0 top-0 z-30 bg-gray-50 px-2 py-1.5 text-left font-medium text-gray-600 border-r border-b border-gray-200" style="min-width: 180px;">
                            Сотрудники
                        </th>
                     @foreach($tasks as $t)
                        <th class="sticky top-0 z-20 px-1.5 py-1.5 text-center border-b border-gray-200 group"
                            style="background-color: {{ $t->color }}10; min-width: 75px; width: 75px;">
                            <a href="{{ route('calendar.show', $t->id) }}" 
                            class="flex flex-col items-center gap-0.5 hover:opacity-70 relative" 
                            title="{{ $t->title }}">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $t->color }}"></span>
                                <span class="truncate w-full">{{ Str::limit($t->title, 8) }}</span>
                                
                                {{-- Тултип с ответственным и датами --}}
                                @php $resp = $t->employeePosition?->employee?->person; @endphp
                                <span class="absolute -bottom-16 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[10px] px-2 py-1.5 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 z-50 pointer-events-none shadow-xl">
                                    @if($resp)
                                        <div class="font-medium">{{ $resp->фамилия }} {{ mb_substr($resp->имя, 0, 1) }}.{{ $resp->отчество ? ' '.mb_substr($resp->отчество, 0, 1).'.' : '' }}</div>
                                    @else
                                        <div class="text-gray-400">Без ответственного</div>
                                    @endif
                                    <div class="text-[9px] text-gray-400 mt-0.5">
                                        {{ $t->start_date?->format('d.m.Y') ?? '—' }}
                                        @if($t->end_date) — {{ $t->end_date->format('d.m.Y') }} @endif
                                    </div>
                                    @if($t->quota)
                                        <div class="text-[9px] text-gray-400">Квота: {{ $t->quota }}</div>
                                    @endif
                                </span>
                            </a>
                        </th>
                    @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrix as $row)
                        @php
                            $e = $row['employee'];
                            $p = $e->person;
                            $name = $p ? $p->фамилия . ' ' . mb_substr($p->имя, 0, 1) . '.' . ($p->отчество ? ' ' . mb_substr($p->отчество, 0, 1) . '.' : '') : '#'.$e->id;
                            $ep = $e->current_ep ?? null;
                            $cp = $ep?->commissariatPosition;
                        @endphp
                        <tr>
                            <td class="sticky left-0 z-10 bg-white px-2 py-1.5 border-r border-b whitespace-nowrap" style="min-width: 180px;">
                                <a href="{{ route('employees.show', $e->id) }}" class="font-medium hover:text-indigo-600 text-[11px]">{{ $name }}</a>
                                @if($cp)
                                    <a href="{{ route('commissariat-positions.show', array_filter(['id' => $cp->id, 'back_url' => url()->full(), 'commissariat_id' => $commissariat->id, 'employeeId' => $e->id])) }}"
                                       class="block text-gray-400 hover:text-indigo-600 text-[10px]">
                                        {{ $cp->position?->name }}
                                    </a>
                                @endif
                            </td>
                            @foreach($tasks as $t)
                                @php $a = $row['tasks'][$t->id] ?? null; @endphp
                                <td class="relative px-1.5 py-1.5 text-center border-b group" style="min-width: 75px; width: 75px;">
                                    @if($a)
                                        @php $pct = $a->quota ? round($a->completed_count / $a->quota * 100) : 0; @endphp
                                        <div class="text-[10px] font-semibold {{ $pct >= 100 ? 'text-emerald-600' : ($pct > 50 ? 'text-indigo-600' : 'text-amber-600') }}">
                                            {{ $a->completed_count }}/{{ $a->quota }}
                                        </div>
                                        <div class="mt-0.5 w-full bg-gray-200 rounded-full h-1">
                                            <div class="h-1 rounded-full {{ $pct >= 100 ? 'bg-emerald-500' : ($pct > 50 ? 'bg-indigo-500' : 'bg-amber-500') }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <div class="text-[9px] text-gray-400">P{{ $a->priority }}</div>
                                    @else
                                        <span class="text-gray-300 text-sm">—</span>
                                       <a href="{{ route('calendar.assignments.create', [$t->id, $e->id]) }}"
   class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100">
    <span class="px-1 py-0.5 text-[9px] text-white bg-indigo-500 hover:bg-indigo-600 rounded">+</span>
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