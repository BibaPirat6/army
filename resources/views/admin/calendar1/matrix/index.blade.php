@extends('layouts.main')

@section('header-title', 'Матрица — ' . $commissariat->name)

@section('content')
<div class="px-3 py-3">
    {{-- Header --}}
    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-800">
                {{ $commissariat->name }}
            </h1>

            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                <span class="rounded-full bg-indigo-50 px-2 py-1 text-indigo-600">
                    Задач: {{ count($tasks) }}
                </span>

                <span class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-600">
                    Сотрудников: {{ count($matrix) }}
                </span>
            </div>
        </div>

        {{-- Zoom --}}
        <div class="flex items-center gap-2">
            <button type="button"
                    onclick="changeScale(-20)"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">
                −
            </button>

            <span id="zoomLabel" class="min-w-[60px] text-center text-sm font-medium text-gray-600">
                140%
            </span>

            <button type="button"
                    onclick="changeScale(20)"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">
                +
            </button>
        </div>
    </div>

    @if($tasks->isEmpty() || empty($matrix))
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <div class="text-sm text-gray-400">
                Нет данных
            </div>
        </div>
    @else

        <div class="overflow-auto rounded-2xl border border-gray-200 bg-white shadow-sm"
             style="max-height: 82vh;">

            <table id="matrixTable"
                   class="border-collapse text-sm transition-all duration-200">
                <thead>
                    <tr class="bg-gray-50">

                        {{-- Employees --}}
                        <th class="sticky left-0 top-0 z-40 border-b border-r border-gray-200 bg-gray-50 px-4 py-3 text-left"
                            style="min-width: 260px;">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Сотрудники
                                </span>
                            </div>
                        </th>

                        {{-- Tasks --}}
                        @foreach($tasks as $t)

                            <th class="task-column sticky top-0 z-30 border-b border-gray-200 group relative"
                                style="
                                    background-color: {{ $t->color }}12;
                                    min-width: 180px;
                                    width: 180px;
                                ">

                                {{-- Resize Handle --}}
                                <div class="resize-handle absolute right-0 top-0 h-full w-1 cursor-col-resize hover:bg-indigo-400"></div>

                                <a href="{{ route('calendar.show', $t->id) }}"
                                   class="flex h-full flex-col items-start gap-2 px-3 py-3 hover:bg-black/5 transition"
                                   title="{{ $t->title }}">

                                    <div class="flex items-center gap-2 w-full">
                                        <span class="h-2.5 w-2.5 rounded-full flex-shrink-0"
                                              style="background: {{ $t->color }}"></span>

                                        <span class="line-clamp-2 text-left text-xs font-semibold leading-4 text-gray-700">
                                            {{ $t->title }}
                                        </span>
                                    </div>

                                    @php
                                        $resp = $t->employeePosition?->employee?->person;
                                    @endphp

                                    <div class="text-[10px] text-gray-400">
                                        {{ $t->start_date?->format('d.m.Y') ?? '—' }}
                                        @if($t->end_date)
                                            — {{ $t->end_date->format('d.m.Y') }}
                                        @endif
                                    </div>

                                    {{-- Tooltip --}}
                                    <div class="pointer-events-none absolute left-1/2 top-full z-50 mt-2 w-72 -translate-x-1/2 rounded-xl bg-gray-900 p-3 text-left opacity-0 shadow-2xl transition-all duration-200 group-hover:opacity-100">

                                        <div class="mb-2 text-sm font-semibold text-white break-words">
                                            {{ $t->title }}
                                        </div>

                                        @if($resp)
                                            <div class="text-xs text-gray-300">
                                                Ответственный:
                                                {{ $resp->фамилия }}
                                                {{ mb_substr($resp->имя, 0, 1) }}.
                                                @if($resp->отчество)
                                                    {{ mb_substr($resp->отчество, 0, 1) }}.
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400">
                                                Без ответственного
                                            </div>
                                        @endif

                                        @if($t->quota)
                                            <div class="mt-1 text-xs text-indigo-300">
                                                Квота: {{ $t->quota }}
                                            </div>
                                        @endif
                                    </div>
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

                            $name = $p
                                ? $p->фамилия . ' ' .
                                  mb_substr($p->имя, 0, 1) . '.' .
                                  ($p->отчество ? ' ' . mb_substr($p->отчество, 0, 1) . '.' : '')
                                : '#'.$e->id;

                            $ep = $e->current_ep ?? null;
                            $cp = $ep?->commissariatPosition;
                        @endphp

                        <tr class="hover:bg-gray-50/70 transition">
                            {{-- Employee --}}
                            <td class="sticky left-0 z-20 border-b border-r border-gray-100 bg-white px-4 py-3"
                                style="min-width: 260px;">

                                <div class="flex flex-col">
                                    <a href="{{ route('employees.show', $e->id) }}"
                                       class="font-semibold text-gray-700 hover:text-indigo-600">
                                        {{ $name }}
                                    </a>

                                    @if($cp)
                                        <a href="{{ route('commissariat-positions.show', array_filter([
                                            'id' => $cp->id,
                                            'back_url' => url()->full(),
                                            'commissariat_id' => $commissariat->id,
                                            'employeeId' => $e->id
                                        ])) }}"
                                           class="mt-1 text-xs text-gray-400 hover:text-indigo-600">
                                            {{ $cp->position?->name }}
                                        </a>
                                        {{-- Добавь ссылку на график --}}
                                        <a href="{{ route('calendar.schedule.employee', $e->id) }}" class="block text-gray-400 hover:text-emerald-600 text-[10px]">
                                            📅 График
                                        </a>
                                    @endif
                                </div>
                            </td>

                            {{-- Tasks --}}
                            @foreach($tasks as $t)

                                @php
                                    $a = $row['tasks'][$t->id] ?? null;
                                @endphp

                                <td class="border-b border-gray-100 px-3 py-3 align-top"
                                    style="min-width: 180px; width: 180px;">

                                    @if($a)

                                        @php
                                            $pct = $a->quota
                                                ? round($a->completed_count / $a->quota * 100)
                                                : 0;
                                        @endphp

                                        <a href="{{ route('calendar.assignments.edit', [$t->id, $a->id]) }}"
                                           class="group block rounded-xl border border-gray-100 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">

                                            <div class="mb-2 flex items-center justify-between">
                                                <div class="text-sm font-bold
                                                    {{ $pct >= 100
                                                        ? 'text-emerald-600'
                                                        : ($pct > 50
                                                            ? 'text-indigo-600'
                                                            : 'text-amber-600') }}">
                                                    {{ $a->completed_count }}/{{ $a->quota }}
                                                </div>

                                                <div class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-500">
                                                    P{{ $a->priority }}
                                                </div>
                                            </div>

                                            <div class="mb-2 h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full rounded-full transition-all
                                                    {{ $pct >= 100
                                                        ? 'bg-emerald-500'
                                                        : ($pct > 50
                                                            ? 'bg-indigo-500'
                                                            : 'bg-amber-500') }}"
                                                     style="width: {{ min($pct, 100) }}%">
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between text-[11px]">
                                                <span class="text-gray-400">
                                                    Выполнение
                                                </span>

                                                <span class="font-medium text-gray-600">
                                                    {{ $pct }}%
                                                </span>
                                            </div>
                                        </a>

                                    @else

                                        <div class="group relative flex min-h-[92px] items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50 hover:border-indigo-300 hover:bg-indigo-50 transition">

                                            <span class="text-gray-300 text-lg">
                                                —
                                            </span>

                                            <a href="{{ route('calendar.assignments.create', [$t->id, $e->id]) }}"
                                               class="absolute inset-0 flex items-center justify-center opacity-0 transition group-hover:opacity-100">

                                                <span class="rounded-lg bg-indigo-500 px-3 py-1 text-xs font-medium text-white shadow hover:bg-indigo-600">
                                                    Назначить
                                                </span>
                                            </a>
                                        </div>

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

{{-- Resize + Zoom --}}
<script>
    let currentScale = 140;

    function changeScale(diff) {
        currentScale += diff;

        if (currentScale < 60) currentScale = 60;
        if (currentScale > 220) currentScale = 220;

        const table = document.getElementById('matrixTable');

        table.style.fontSize = (currentScale / 100) + 'rem';

        document.querySelectorAll('.task-column').forEach(col => {
            const width = 180 * (currentScale / 100);

            col.style.minWidth = width + 'px';
            col.style.width = width + 'px';
        });

        document.querySelectorAll('tbody td').forEach(td => {
            if (!td.classList.contains('sticky')) {
                const width = 180 * (currentScale / 100);

                td.style.minWidth = width + 'px';
                td.style.width = width + 'px';
            }
        });

        document.getElementById('zoomLabel').innerText = currentScale + '%';
    }

    // Resize columns
    document.querySelectorAll('.resize-handle').forEach(handle => {

        handle.addEventListener('mousedown', function(e) {

            e.preventDefault();

            const th = handle.parentElement;

            let startX = e.pageX;
            let startWidth = th.offsetWidth;

            function mouseMove(e) {

                let newWidth = startWidth + (e.pageX - startX);

                if (newWidth < 120) {
                    newWidth = 120;
                }

                th.style.width = newWidth + 'px';
                th.style.minWidth = newWidth + 'px';

                const index = Array.from(th.parentNode.children).indexOf(th);

                document.querySelectorAll('#matrixTable tr').forEach(row => {

                    const cell = row.children[index];

                    if (cell) {
                        cell.style.width = newWidth + 'px';
                        cell.style.minWidth = newWidth + 'px';
                    }
                });
            }

            function mouseUp() {
                document.removeEventListener('mousemove', mouseMove);
                document.removeEventListener('mouseup', mouseUp);
            }

            document.addEventListener('mousemove', mouseMove);
            document.addEventListener('mouseup', mouseUp);
        });
    });
</script>
@endsection