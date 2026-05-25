@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-5">

        {{-- HEADER --}}
        <div class="flex justify-between items-start">

            <div class="space-y-1">
                <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Назад к календарю
                </a>
                <a href="{{ route('calendar.matrix.index', $employee->employeePosition?->commissariatPosition?->commissariat_id ?? 1) }}"
                    class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Назад к матрице
                </a>
                <h1 class="text-xl font-bold tracking-tight">
                    {{ $employee->person->фамилия }} {{ $employee->person->имя }}
                </h1>

                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="px-2 py-0.5 bg-gray-100 rounded">
                        {{ $month }}/{{ $year }}
                    </span>
                    <span>•</span>
                    <span>график сотрудника</span>
                </div>
            </div>

            <a href="{{ route('calendar.schedule.setup', $employee->id) }}"
                class="px-4 py-2 text-xs font-medium bg-indigo-600 text-white rounded-lg
                      hover:bg-indigo-700 active:scale-95 transition-all duration-200 shadow-sm">
                ⚙ Настроить
            </a>
        </div>

        {{-- NAV --}}
        @php
            $prev = \Carbon\Carbon::create($year, $month)->subMonth();
            $next = \Carbon\Carbon::create($year, $month)->addMonth();
        @endphp

        <div class="flex items-center gap-2 text-sm bg-white border rounded-xl p-2 shadow-sm">

            <a href="?month={{ $prev->month }}&year={{ $prev->year }}"
                class="px-3 py-1 rounded-lg hover:bg-gray-100 transition">
                ←
            </a>

            <select onchange="go(this.value, {{ $year }})" class="border-0 bg-transparent text-sm focus:ring-0">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($m == $month)>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <select onchange="go({{ $month }}, this.value)" class="border-0 bg-transparent text-sm">
                @foreach (range(now()->year - 1, now()->year + 2) as $y)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
            </select>

            <a href="?month={{ $next->month }}&year={{ $next->year }}"
                class="px-3 py-1 rounded-lg hover:bg-gray-100 transition">
                →
            </a>

        </div>

        {{-- TABLE WRAPPER --}}
        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">

            <table class="w-full text-xs">

                {{-- sticky header --}}
                <thead class="sticky top-0 bg-gray-50 border-b text-gray-600">
                    <tr>
                        <th class="p-3 text-left">Дата</th>
                        <th class="p-3 text-left">Тип</th>
                        <th class="p-3 text-left">Время</th>
                        <th class="p-3 text-left">Задачи</th>
                        <th class="p-3 text-center">Загрузка</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($schedule['plan'] as $date => $day)
                        @php
                            $wd = $day['work_day'];
                            $isWork = $wd && $wd->type === 'рабочий_день';
                            $total = $wd?->total_minutes ?? 0;

                            // РЕАЛЬНАЯ загрузка из planner
                            $pct = $day['load_percent'] ?? 0;
                        @endphp

                        <tr
                            class="group border-b transition-all duration-200
                                   hover:bg-gray-50 hover:shadow-sm
                                   {{ !$isWork ? 'bg-gray-50/50' : '' }}">

                            {{-- DATE --}}
                            <td class="p-3 font-medium">
                                <div class="flex flex-col">
                                    <span class="text-sm">
                                        {{ \Carbon\Carbon::parse($date)->format('d') }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}
                                    </span>
                                </div>
                            </td>

                            {{-- TYPE --}}
                            <td class="p-3">
                                <span
                                    class="px-2 py-1 rounded-lg text-[10px]
                                    {{ $isWork ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $isWork ? 'Рабочий' : 'Выходной' }}
                                </span>
                            </td>

                            {{-- TIME --}}
                            <td class="p-3 text-gray-600">
                                @if ($wd && $wd->work_start)
                                    <div class="flex flex-col">
                                        <span>
                                            {{ $wd->work_start }} — {{ $wd->work_end }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">
                                            {{ $total }} мин доступно
                                        </span>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>


                            {{-- TASKS Column --}}
                            <td class="p-3 align-top">
                                <div class="flex flex-col gap-1">
                                    @forelse($day['task_meta'] as $taskId => $meta)
                                        @php
                                            $minutes = $meta['minutes'] ?? 0;
                                            $taskName = $meta['task_name'] ?? 'Задача';
                                            $remainingQuota = $meta['remaining_quota'] ?? 0;
                                            $assignmentId = $day['tasks'][$taskId]['assignment_id'] ?? null;

                                            // Цвет бейджа (просто синий для всех, чтобы не мельтешило)
                                            $badgeClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                                        @endphp

                                        @if ($assignmentId)
                                            <a href="{{ route('calendar.assignments.edit', [$taskId, $assignmentId]) }}"
                                                class="block px-2 py-1.5 rounded-md text-xs transition hover:bg-blue-100 {{ $badgeClass }}">
                                                <div class="font-semibold truncate" title="{{ $taskName }}">
                                                    {{ $taskName }}
                                                </div>
                                                <div
                                                    class="flex justify-between items-center mt-0.5 text-[10px] text-blue-600/80">
                                                    <span>{{ $minutes }} мин</span>
                                                    @if ($remainingQuota > 0)
                                                        <span>ост. {{ $remainingQuota }}</span>
                                                    @endif
                                                </div>
                                            </a>
                                        @else
                                            <div
                                                class="px-2 py-1 rounded-md text-xs bg-gray-50 text-gray-500 border border-gray-200">
                                                {{ $taskName }} ({{ $minutes }} мин)
                                            </div>
                                        @endif
                                    @empty
                                        <span class="text-gray-300 text-sm">—</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- PROGRESS / LOAD Column --}}
                            <td class="p-3 align-middle">
                                @php
                                    $pct = $day['load_percent'] ?? 0;
                                    $color =
                                        $pct > 100 ? 'bg-red-500' : ($pct > 85 ? 'bg-amber-500' : 'bg-emerald-500');
                                @endphp

                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $color }} transition-all duration-500"
                                            style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-medium text-gray-600">{{ $pct }}%</span>
                                </div>
                            </td>


                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>
    </div>

    <script>
        function go(month, year) {
            window.location = `?month=${month}&year=${year}`;
        }
    </script>
@endsection
