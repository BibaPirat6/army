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
            <button type="button" onclick="changeScale(-20)"
                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">
                −
            </button>
            <span id="zoomLabel" class="min-w-[60px] text-center text-sm font-medium text-gray-600">100%</span>
            <button type="button" onclick="changeScale(20)"
                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm hover:bg-gray-50">
                +
            </button>
        </div>
    </div>

    @if($tasks->isEmpty() || empty($matrix))
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <div class="text-sm text-gray-400">Нет данных</div>
        </div>
    @else
        <div class="overflow-auto rounded-2xl border border-gray-200 bg-white shadow-sm" style="max-height: 82vh;">
            <table id="matrixTable" class="border-collapse text-sm transition-all duration-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="sticky left-0 top-0 z-40 border-b border-r border-gray-200 bg-gray-50 px-4 py-3 text-left" style="min-width: 200px;">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Сотрудники</span>
                        </th>
                        @foreach($tasks as $t)
                            <th class="task-column sticky top-0 z-30 border-b border-gray-200 group relative"
                                style="background-color: {{ $t->color }}12; min-width: 160px; width: 160px;">
                                <div class="resize-handle absolute right-0 top-0 h-full w-1 cursor-col-resize hover:bg-indigo-400"></div>
                                <a href="{{ route('calendar.tasks.show', $t->id) }}"
                                   class="flex h-full flex-col items-start gap-1 px-2 py-2 hover:bg-black/5 transition"
                                   title="{{ $t->title }}">
                                    <div class="flex items-center gap-1.5 w-full">
                                        <span class="h-2 w-2 rounded-full flex-shrink-0" style="background: {{ $t->color }}"></span>
                                        <span class="line-clamp-2 text-left text-[11px] font-semibold leading-4 text-gray-700">{{ $t->title }}</span>
                                    </div>
                                    <div class="text-[9px] text-gray-400">
                                        {{ $t->start_date?->format('d.m.Y') ?? '—' }}
                                        @if($t->end_date) — {{ $t->end_date->format('d.m.Y') }} @endif
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
                            $name = $p ? trim($p->фамилия . ' ' . mb_substr($p->имя, 0, 1) . '.' . ($p->отчество ? ' ' . mb_substr($p->отчество, 0, 1) . '.' : '')) : '#'.$e->id;
                            $ep = $e->current_ep ?? null;
                            $cp = $ep?->commissariatPosition;
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="sticky left-0 z-20 border-b border-r border-gray-100 bg-white px-3 py-2" style="min-width: 200px;">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-r from-indigo-100 to-indigo-200 flex items-center justify-center text-indigo-700 font-semibold text-xs flex-shrink-0">
                                            {{ mb_substr($name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('employees.show', $e->id) }}" class="font-semibold text-gray-700 hover:text-indigo-600 text-sm">
                                                {{ $name }}
                                            </a>
                                            @if($cp)
                                                <div class="text-[10px]">
                                                    <a href="{{ route('commissariat-positions.show', array_filter([
                                                        'id' => $cp->id,
                                                        'back_url' => url()->full(),
                                                        'commissariat_id' => $commissariat->id,
                                                        'employeeId' => $e->id
                                                    ])) }}" class="text-gray-500 hover:text-indigo-600 transition">
                                                        {{ $cp->position?->name ?? '' }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @if($cp)
                                        <div class="mt-1">
                                            <a href="{{ route('calendar.schedule.employee', $e->id) }}" class="text-[9px] text-gray-400 hover:text-emerald-600">
                                                📅 График
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            @foreach($tasks as $t)
                                @php $a = $row['tasks'][$t->id] ?? null; @endphp
                                <td class="border-b border-gray-100 px-1 py-1 align-top" style="min-width: 160px; width: 160px;">
                                    @if($a)
                                        @php $pct = $a->quota ? round($a->completed_count / $a->quota * 100) : 0; @endphp
                                        <a href="{{ route('calendar.assignments.edit', [$t->id, $a->id]) }}"
                                           class="group block rounded-lg border border-gray-100 bg-white p-2 shadow-sm transition hover:shadow-md">
                                            <div class="mb-1 flex items-center justify-between">
                                                <div class="text-xs font-bold {{ $pct >= 100 ? 'text-emerald-600' : ($pct > 50 ? 'text-indigo-600' : 'text-amber-600') }}">
                                                    {{ $a->completed_count }}/{{ $a->quota }}
                                                </div>
                                                <div class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[9px] text-gray-500">P{{ $a->priority }}</div>
                                            </div>
                                            <div class="mb-1 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full rounded-full transition-all {{ $pct >= 100 ? 'bg-emerald-500' : ($pct > 50 ? 'bg-indigo-500' : 'bg-amber-500') }}" style="width: {{ min($pct, 100) }}%"></div>
                                            </div>
                                            <div class="flex items-center justify-between text-[9px]">
                                                <span class="text-gray-400">Выполнение</span>
                                                <span class="font-medium text-gray-600">{{ $pct }}%</span>
                                            </div>
                                        </a>
                                    @else
                                        <div class="group relative flex min-h-[70px] items-center justify-center rounded-lg border border-dashed border-gray-200 bg-gray-50 hover:border-indigo-300 hover:bg-indigo-50 transition">
                                            <span class="text-gray-300 text-base">—</span>
                                            <a href="{{ route('calendar.assignments.create', [$t->id, $e->id]) }}"
                                               class="absolute inset-0 flex items-center justify-center opacity-0 transition group-hover:opacity-100">
                                                <span class="rounded-lg bg-indigo-500 px-2 py-1 text-[10px] font-medium text-white shadow hover:bg-indigo-600 whitespace-nowrap">
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

<style>
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .task-column { position: relative; transition: background-color 0.2s; }
    .resize-handle { position: absolute; right: 0; top: 0; width: 4px; height: 100%; cursor: col-resize; background: transparent; z-index: 30; }
    .resize-handle:hover { background: rgba(99, 102, 241, 0.5); }
    .resize-handle:active { background: rgb(99, 102, 241); }
    .overflow-auto::-webkit-scrollbar { height: 8px; width: 8px; }
    .overflow-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .overflow-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .overflow-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    body.resizing { user-select: none; cursor: col-resize; }
</style>

<script>
    let currentScale = 100;
    let activeResize = null;
    let savedWidths = JSON.parse(localStorage.getItem('matrixWidths') || '{}');
    const BASE_EMPLOYEE_WIDTH = 200;
    const BASE_TASK_WIDTH = 160;

    function saveWidth(column, width) { savedWidths[column] = width; localStorage.setItem('matrixWidths', JSON.stringify(savedWidths)); }
    function getWidth(column, defaultWidth) { return savedWidths[column] || defaultWidth; }

    function applyScale() {
        const scale = currentScale / 100;
        document.getElementById('zoomLabel').innerText = currentScale + '%';
        const empWidth = Math.round(BASE_EMPLOYEE_WIDTH * scale);
        const empCol = document.querySelector('#matrixTable thead th:first-child');
        if (empCol) { empCol.style.minWidth = empWidth + 'px'; empCol.style.width = empWidth + 'px'; }
        document.querySelectorAll('#matrixTable tbody td:first-child').forEach(td => { td.style.minWidth = empWidth + 'px'; td.style.width = empWidth + 'px'; });
        document.querySelectorAll('.task-column').forEach((col, idx) => {
            const defaultWidth = Math.round(BASE_TASK_WIDTH * scale);
            const savedWidth = getWidth(idx, defaultWidth);
            const finalWidth = Math.max(120, Math.min(400, savedWidth));
            col.style.minWidth = finalWidth + 'px'; col.style.width = finalWidth + 'px';
            document.querySelectorAll(`#matrixTable tbody tr td:nth-child(${idx + 2})`).forEach(td => { td.style.minWidth = finalWidth + 'px'; td.style.width = finalWidth + 'px'; });
        });
    }

    function initResize() {
        document.querySelectorAll('.resize-handle').forEach((handle, idx) => {
            handle.removeEventListener('mousedown', onResizeStart);
            handle.addEventListener('mousedown', (e) => onResizeStart(e, idx));
        });
    }

    function onResizeStart(e, columnIndex) {
        e.preventDefault(); e.stopPropagation();
        const th = e.target.closest('.task-column');
        if (!th) return;
        const startX = e.clientX, startWidth = th.offsetWidth;
        activeResize = { columnIndex, th, startX, startWidth };
        document.body.classList.add('resizing');
        document.addEventListener('mousemove', onResizeMove);
        document.addEventListener('mouseup', onResizeEnd);
    }

    function onResizeMove(e) {
        if (!activeResize) return;
        const delta = e.clientX - activeResize.startX;
        let newWidth = activeResize.startWidth + delta;
        newWidth = Math.max(120, Math.min(400, newWidth));
        activeResize.th.style.width = newWidth + 'px';
        activeResize.th.style.minWidth = newWidth + 'px';
        document.querySelectorAll(`#matrixTable tbody tr td:nth-child(${activeResize.columnIndex + 2})`).forEach(td => {
            td.style.width = newWidth + 'px'; td.style.minWidth = newWidth + 'px';
        });
        saveWidth(activeResize.columnIndex, newWidth);
    }

    function onResizeEnd() { activeResize = null; document.body.classList.remove('resizing'); document.removeEventListener('mousemove', onResizeMove); document.removeEventListener('mouseup', onResizeEnd); }
    function changeScale(diff) { currentScale = Math.min(180, Math.max(60, currentScale + diff)); applyScale(); }

    document.addEventListener('DOMContentLoaded', () => { initResize(); applyScale(); });
    const observer = new MutationObserver(() => { initResize(); applyScale(); });
    const table = document.getElementById('matrixTable');
    if (table) observer.observe(table, { childList: true, subtree: true });
</script>
@endsection