@extends('layouts.main')

@section('header-title', 'График — ' . $employee->person->фамилия)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-slate-100">
        <div class="max-w-7xl mx-auto p-4 sm:p-6 space-y-5">

            {{-- HEADER — улучшенные отступы и иерархия --}}
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                <div class="space-y-2">
                    {{-- Хлебные крошки --}}
                    <nav class="flex items-center gap-1.5 text-sm text-gray-500" aria-label="Breadcrumb">
                        <a href="{{ route('calendar.index') }}"
                            class="hover:text-indigo-600 transition-colors duration-200 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
                            </svg>
                            Календарь
                        </a>
                        <svg class="w-3 h-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" />
                        </svg>
                        <a href="{{ route('calendar.matrix.index', $employee->employeePosition?->commissariatPosition?->commissariat_id ?? 1) }}"
                            class="hover:text-indigo-600 transition-colors duration-200">
                            Матрица
                        </a>
                        <svg class="w-3 h-3 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" />
                        </svg>
                        <span class="text-gray-900 font-medium truncate">
                            {{ $employee->person->фамилия }} {{ $employee->person->имя }}
                        </span>
                    </nav>

                    {{-- Информационная плашка --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-full text-xs font-medium text-gray-700 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-full text-xs font-medium text-indigo-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            График сотрудника
                        </span>
                    </div>
                </div>

                {{-- Кнопка настройки — улучшенный дизайн --}}
                <a href="{{ route('calendar.schedule.setup', $employee->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-medium rounded-xl
                      hover:from-indigo-700 hover:to-indigo-800 active:scale-95 transition-all duration-200 shadow-md shadow-indigo-500/20 hover:shadow-lg hover:shadow-indigo-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Настроить график
                </a>
            </div>

            {{-- NAV — улучшенная навигация по месяцам --}}
            @php
                $prev = \Carbon\Carbon::create($year, $month)->subMonth();
                $next = \Carbon\Carbon::create($year, $month)->addMonth();
                $currentPeriod = \Carbon\Carbon::create($year, $month, 1);
            @endphp

            <div class="flex items-center justify-between bg-white border border-gray-200 rounded-2xl p-2 shadow-sm">
                <a href="?month={{ $prev->month }}&year={{ $prev->year }}"
                    class="flex items-center justify-center w-10 h-10 rounded-xl hover:bg-gray-100 transition-all duration-200 group"
                    title="Предыдущий месяц">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-900 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <div class="flex items-center gap-1.5">
                    <select onchange="navigateToMonth(this.value, {{ $year }})"
                        class="appearance-none bg-gray-50 border-0 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-indigo-500 cursor-pointer hover:bg-gray-100 transition-colors">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($m == $month)>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>

                    <select onchange="navigateToMonth({{ $month }}, this.value)"
                        class="appearance-none bg-gray-50 border-0 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-900 focus:ring-2 focus:ring-indigo-500 cursor-pointer hover:bg-gray-100 transition-colors">
                        @foreach (range(now()->year - 2, now()->year + 2) as $y)
                            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="?month={{ $next->month }}&year={{ $next->year }}"
                    class="flex items-center justify-center w-10 h-10 rounded-xl hover:bg-gray-100 transition-all duration-200 group"
                    title="Следующий месяц">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-900 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- TABLE — улучшенная таблица с карточками для мобильных --}}
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gradient-to-r from-gray-50 to-slate-50 border-b-2 border-gray-100">
                            <tr>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Дата
                                </th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Тип
                                </th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Рабочее время</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Задачи</th>
                                <th class="p-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Загрузка</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($schedule['plan'] as $date => $day)
                                @php
                                    $wd = $day['work_day'];
                                    $isWork = $wd && $wd->type === 'рабочий_день';
                                    $total = $wd?->total_minutes ?? 0;
                                    $pct = $day['load_percent'] ?? 0;
                                    $carbonDate = \Carbon\Carbon::parse($date);
                                    $isToday = $carbonDate->isToday();
                                @endphp
                                <tr
                                    class="group transition-all duration-200 
                                       {{ $isWork ? 'hover:bg-indigo-50/30' : 'bg-gray-50/50 hover:bg-gray-100/50' }}
                                       {{ $isToday ? 'ring-2 ring-indigo-200 ring-inset bg-indigo-50/20' : '' }}">

                                    {{-- DATE --}}
                                    <td class="p-4">
                                        <a href="{{ route('calendar.schedule.timeline', ['employee' => $employee->id, 'date' => $date]) }}"
                                            class="block">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex flex-col items-center justify-center w-10 h-10 rounded-xl 
                                                        {{ $isToday ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-gray-100 text-gray-900' }}">
                                                    <span
                                                        class="text-sm font-bold leading-none">{{ $carbonDate->format('d') }}</span>
                                                    <span
                                                        class="text-[9px] leading-none mt-0.5 opacity-70">{{ $carbonDate->translatedFormat('D') }}</span>
                                                </div>
                                                @if ($isToday)
                                                    <span
                                                        class="text-[10px] font-medium text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full">Сегодня</span>
                                                @endif
                                            </div>
                                        </a>
                                    </td>

                                    {{-- TYPE --}}
                                    <td class="p-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium
                                        {{ $isWork
                                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                            : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full {{ $isWork ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                            {{ $isWork ? 'Рабочий' : 'Выходной' }}
                                        </span>
                                    </td>

                                    {{-- TIME --}}
                                    <td class="p-4">
                                        @if ($wd && $wd->work_start)
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span class="font-medium text-gray-900">{{ $wd->work_start }} —
                                                        {{ $wd->work_end }}</span>
                                                </div>
                                                <span class="text-[10px] text-gray-500 ml-5">{{ $total }} мин
                                                    доступно</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    {{-- TASKS --}}
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse($day['task_meta'] as $taskId => $meta)
                                                @php
                                                    $minutes = $meta['minutes'] ?? 0;
                                                    $taskName = $meta['task_name'] ?? 'Задача';
                                                    $remainingQuota = $meta['remaining_quota'] ?? 0;
                                                    $completedCount = $meta['completed_count'] ?? 0;
                                                    $quotaTotal = $meta['quota_total'] ?? 0;
                                                    $assignmentId = $day['tasks'][$taskId]['assignment_id'] ?? null;
                                                    $percent =
                                                        $quotaTotal > 0
                                                            ? min(100, ($completedCount / $quotaTotal) * 100)
                                                            : 0;
                                                    $isOverdue = $remainingQuota > 0 && $carbonDate->isPast();
                                                @endphp

                                                @if ($assignmentId)
                                                    <div onclick="openTaskModal({{ $assignmentId }}, '{{ $date }}')"
                                                        class="group/task cursor-pointer px-3 py-2 rounded-xl transition-all duration-200
                                                            bg-gradient-to-br from-blue-50 to-indigo-50 
                                                            text-blue-800 border border-blue-200 
                                                            hover:shadow-md hover:shadow-blue-200/50 hover:-translate-y-0.5
                                                            {{ $isOverdue ? 'ring-2 ring-amber-300' : '' }}"
                                                        title="{{ $isOverdue ? 'Просрочено' : 'Нажмите для отметки выполнения' }}">

                                                        <div class="flex items-start justify-between gap-2">
                                                            <span
                                                                class="text-xs font-semibold truncate max-w-[120px]">{{ $taskName }}</span>
                                                            @if ($isOverdue)
                                                                <span class="flex-shrink-0 text-amber-600">⚠️</span>
                                                            @endif
                                                        </div>

                                                        <div class="flex items-center justify-between mt-1.5 text-[10px]">
                                                            <span
                                                                class="text-blue-600/80 font-medium mr-1">{{ $minutes }}
                                                                мин </span>
                                                            @if ($remainingQuota > 0)
                                                                <span class="text-blue-600/60">квота:
                                                                    {{ $remainingQuota }}</span>
                                                            @endif
                                                        </div>

                                                        {{-- Прогресс-бар --}}
                                                        <div
                                                            class="mt-2 h-1.5 bg-blue-200/50 rounded-full overflow-hidden">
                                                            <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500"
                                                                style="width: {{ $percent }}%"></div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div
                                                        class="px-3 py-2 rounded-xl text-xs bg-gray-50 text-gray-500 border border-gray-200">
                                                        <span class="font-medium">{{ $taskName }}</span>
                                                        <span class="text-gray-400 ml-1">({{ $minutes }} мин)</span>
                                                    </div>
                                                @endif
                                            @empty
                                                <span class="text-gray-300 text-sm italic">Нет задач</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    {{-- PROGRESS --}}
                                    <td class="p-4">
                                        @php
                                            $pct = $day['load_percent'] ?? 0;
                                            $barColor =
                                                $pct > 100
                                                    ? 'from-red-500 to-red-600'
                                                    : ($pct > 85
                                                        ? 'from-amber-500 to-orange-500'
                                                        : 'from-emerald-500 to-green-500');
                                            $textColor =
                                                $pct > 100
                                                    ? 'text-red-600'
                                                    : ($pct > 85
                                                        ? 'text-amber-600'
                                                        : 'text-emerald-600');
                                        @endphp
                                        <div class="flex flex-col items-center gap-2 min-w-[80px]">
                                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                                                <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full transition-all duration-700 ease-out"
                                                    style="width: {{ min(100, $pct) }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold {{ $textColor }}">
                                                {{ $pct }}%
                                                @if ($pct > 100)
                                                    <span class="text-[10px] text-red-400">⚠</span>
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @foreach ($schedule['plan'] as $date => $day)
                        @php
                            $wd = $day['work_day'];
                            $isWork = $wd && $wd->type === 'рабочий_день';
                            $pct = $day['load_percent'] ?? 0;
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $isToday = $carbonDate->isToday();
                        @endphp
                        <div
                            class="p-4 space-y-3 {{ $isWork ? '' : 'bg-gray-50/50' }} {{ $isToday ? 'border-l-4 border-indigo-500' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl {{ $isToday ? 'bg-indigo-600 text-white' : 'bg-gray-100' }} flex items-center justify-center">
                                        <span class="text-sm font-bold">{{ $carbonDate->format('d') }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $carbonDate->translatedFormat('D') }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $carbonDate->format('d.m.Y') }}</div>
                                    </div>
                                </div>
                                <span
                                    class="px-2.5 py-1 rounded-full text-[10px] font-medium
                                {{ $isWork ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $isWork ? 'Рабочий' : 'Выходной' }}
                                </span>
                            </div>

                            @if ($wd && $wd->work_start)
                                <div class="text-xs text-gray-600 bg-gray-50 rounded-lg p-2">
                                    🕐 {{ $wd->work_start }} — {{ $wd->work_end }} ({{ $total }} мин)
                                </div>
                            @endif

                            @if (!empty($day['task_meta']))
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($day['task_meta'] as $taskId => $meta)
                                        @php
                                            $assignmentId = $day['tasks'][$taskId]['assignment_id'] ?? null;
                                        @endphp
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-[10px] {{ $assignmentId ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $meta['task_name'] ?? 'Задача' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r {{ $pct > 100 ? 'from-red-500 to-red-600' : 'from-emerald-500 to-green-500' }} rounded-full"
                                        style="width: {{ min(100, $pct) }}%"></div>
                                </div>
                                <span
                                    class="text-xs font-bold {{ $pct > 100 ? 'text-red-600' : 'text-gray-600' }}">{{ $pct }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- МОДАЛЬНОЕ ОКНО — улучшенный дизайн --}}
    <div id="completionModal"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-all duration-300"
        role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0"
            id="modalContent">

            {{-- Заголовок --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Отметка выполнения</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Укажите количество выполненных итераций</p>
                </div>
                <button onclick="closeTaskModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Тело --}}
            <div class="p-5 space-y-4">
                {{-- Информация о задаче --}}
                <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl p-4 space-y-2 border border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📋</span>
                        <span class="font-semibold text-gray-900" id="infoTaskName">—</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span>👤</span>
                        <span id="infoEmployee">—</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span>📅</span>
                        <span id="infoDate">—</span>
                    </div>
                    <div class="flex items-center gap-4 mt-2 pt-2 border-t border-gray-200">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-gray-500">Квота:</span>
                            <span class="text-sm font-bold text-indigo-600" id="infoQuota">0</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-gray-500">Время/шт:</span>
                            <span class="text-sm font-bold text-indigo-600" id="infoTime">0</span>
                            <span class="text-[10px] text-gray-500">мин</span>
                        </div>
                    </div>
                </div>

                {{-- Счетчик --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Выполнено итераций</label>

                    <div class="flex items-stretch gap-2">
                        <button type="button" onclick="changeTaskCompleted(-1)"
                            class="w-12 flex items-center justify-center bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-all duration-200 text-xl font-bold active:scale-95"
                            aria-label="Уменьшить">
                            −
                        </button>

                        <input type="number" id="completedInput" min="0" value="0"
                            onchange="validateCompletedCount()" onkeydown="if(event.key==='Enter')saveTaskCompletion()"
                            class="flex-1 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl py-3 
                                  focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all duration-200
                                  hover:border-gray-300"
                            aria-label="Количество выполненных итераций">

                        <button type="button" onclick="changeTaskCompleted(1)"
                            class="w-12 flex items-center justify-center bg-green-50 text-green-600 rounded-xl hover:bg-green-100 transition-all duration-200 text-xl font-bold active:scale-95"
                            aria-label="Увеличить">
                            +
                        </button>
                    </div>

                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xs text-gray-500">Максимум: <strong id="maxQuota"
                                class="text-gray-700">0</strong></span>
                        <button type="button" onclick="setMaxCompleted()"
                            class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                            Заполнить максимум
                        </button>
                    </div>

                    {{-- Быстрый расчет --}}
                    <div id="quickCalc"
                        class="mt-3 p-3 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl border border-indigo-100">
                        <div class="flex justify-between text-xs">
                            <span class="text-indigo-700">Выполнено:</span>
                            <strong class="text-indigo-900" id="calcDone">0</strong>
                            <span class="text-indigo-700">мин</span>
                        </div>
                        <div class="flex justify-between text-xs mt-1">
                            <span class="text-indigo-700">Осталось:</span>
                            <strong class="text-indigo-900" id="calcRemaining">0</strong>
                            <span class="text-indigo-700">мин</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Футер --}}
            <div class="flex items-center justify-between p-5 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
                <a href="#" id="assignmentLink"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    К назначению
                </a>

                <div class="flex gap-2">
                    <button onclick="closeTaskModal()"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl 
                               hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                        Отмена
                    </button>
                    <button onclick="saveTaskCompletion()"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-700 
                               rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 
                               shadow-md shadow-indigo-500/20 active:scale-95 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast уведомления --}}
    <div id="toast"
        class="fixed bottom-5 right-5 z-[60] hidden transform transition-all duration-300 translate-y-4 opacity-0">
        <div class="flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-medium" id="toastContent"></div>
    </div>

    <script>
        // Глобальные переменные
        let currentAssignmentId = null;
        let currentQuota = 0;
        let currentIterationTime = 0;
        let isLoading = false;

        // Навигация по месяцам
        function navigateToMonth(month, year) {
            window.location.href = `?month=${month}&year=${year}`;
        }

        function openTaskModal(assignmentId, date) {
            if (isLoading) return;

            const modal = document.getElementById('completionModal');
            const content = document.getElementById('modalContent');

            // Показываем модалку сразу
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Анимация
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 50);

            // Показываем состояние загрузки
            document.getElementById('infoTaskName').textContent = 'Загрузка...';
            document.getElementById('infoEmployee').textContent = '—';
            document.getElementById('infoDate').textContent = '📅 ' + date;
            document.getElementById('infoQuota').textContent = '...';
            document.getElementById('infoTime').textContent = '...';
            document.getElementById('maxQuota').textContent = '...';
            document.getElementById('completedInput').value = 0;
            document.getElementById('calcDone').textContent = '0';
            document.getElementById('calcRemaining').textContent = '0';

            // Получаем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!csrfToken) {
                showToast('CSRF токен не найден. Обновите страницу.', 'error');
                closeTaskModal();
                return;
            }

            // Загружаем данные
            fetch(`/calendar/schedule/assignment-info/${assignmentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `Ошибка сервера (${response.status})`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Ошибка загрузки данных');
                    }

                    // Заполняем данные
                    currentAssignmentId = data.assignment_id;
                    currentQuota = parseInt(data.quota) || 0;
                    currentIterationTime = parseInt(data.iteration_time) || 0;

                    document.getElementById('infoTaskName').textContent = data.task_name || '—';
                    document.getElementById('infoEmployee').textContent = '👤 ' + (data.employee_name || '—');
                    document.getElementById('infoDate').textContent = '📅 ' + date;
                    document.getElementById('infoQuota').textContent = currentQuota;
                    document.getElementById('infoTime').textContent = currentIterationTime;
                    document.getElementById('maxQuota').textContent = currentQuota;

                    const input = document.getElementById('completedInput');
                    input.value = parseInt(data.completed_count) || 0;
                    input.max = currentQuota;

                    // Ссылка на назначение
                    const link = document.getElementById('assignmentLink');
                    if (link && data.task_id && data.assignment_id) {
                        link.href = `/calendar/tasks/${data.task_id}/assignments/${data.assignment_id}/edit`;
                    }

                    updateCalculation();

                    // Фокус на инпут
                    setTimeout(() => input.focus(), 300);
                })
                .catch(error => {
                    console.error('Ошибка загрузки:', error);
                    showToast('❌ ' + error.message, 'error');
                    closeTaskModal();
                });
        }

        // Функция для диагностики (вызовите в консоли)
        function testAssignmentInfo(id) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch(`/calendar/schedule/assignment-info/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(r => r.json())
                .then(d => console.table(d))
                .catch(e => console.error(e));
        }

        function showModalLoading() {
            document.getElementById('infoTaskName').textContent = 'Загрузка...';
            document.getElementById('infoEmployee').textContent = '—';
            document.getElementById('infoDate').textContent = '—';
            document.getElementById('infoQuota').textContent = '...';
            document.getElementById('infoTime').textContent = '...';
        }

        function closeTaskModal() {
            const modal = document.getElementById('completionModal');
            const content = document.getElementById('modalContent');

            content.classList.add('scale-95', 'opacity-0');
            content.classList.remove('scale-100', 'opacity-100');

            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function changeTaskCompleted(delta) {
            const input = document.getElementById('completedInput');
            let val = parseInt(input.value || 0) + delta;
            input.value = Math.max(0, Math.min(val, currentQuota));
            updateCalculation();
            input.focus();
        }

        function validateCompletedCount() {
            const input = document.getElementById('completedInput');
            let val = parseInt(input.value || 0);
            if (isNaN(val)) val = 0;
            input.value = Math.max(0, Math.min(val, currentQuota));
            updateCalculation();
        }

        function setMaxCompleted() {
            document.getElementById('completedInput').value = currentQuota;
            updateCalculation();
        }

        function updateCalculation() {
            const completed = parseInt(document.getElementById('completedInput').value || 0);
            const remaining = Math.max(0, currentQuota - completed);
            document.getElementById('calcDone').textContent = completed * currentIterationTime;
            document.getElementById('calcRemaining').textContent = remaining * currentIterationTime;
        }

        // Замените функцию saveCompletion на saveTaskCompletion в Blade-шаблоне
        function saveTaskCompletion() {
            if (!currentAssignmentId) {
                showToast('Нет активного задания', 'warning');
                return;
            }

            if (isLoading) return;

            const completedCount = parseInt(document.getElementById('completedInput').value || 0);

            if (completedCount < 0 || completedCount > currentQuota) {
                showToast('Некорректное значение (0 - ' + currentQuota + ')', 'warning');
                return;
            }

            isLoading = true;
            const saveBtn = document.querySelector('button[onclick="saveTaskCompletion()"]');
            const originalHTML = saveBtn.innerHTML;
            saveBtn.innerHTML = `
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        Сохранение...
    `;
            saveBtn.disabled = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!csrfToken) {
                showToast('CSRF токен не найден. Обновите страницу.', 'error');
                resetSaveButton(saveBtn, originalHTML);
                return;
            }

            fetch(`/calendar/schedule/complete/${currentAssignmentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        completed_count: completedCount
                    })
                })
                .then(async response => {
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Ошибка сервера');
                    }

                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showToast('✅ ' + (data.message || 'Выполнение отмечено'), 'success');
                        closeTaskModal();

                        // Плавное обновление страницы
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        throw new Error(data.message || 'Неизвестная ошибка');
                    }
                })
                .catch(error => {
                    console.error('Save completion error:', error);
                    showToast('❌ ' + error.message, 'error');
                })
                .finally(() => {
                    resetSaveButton(saveBtn, originalHTML);
                });
        }

        function resetSaveButton(btn, originalHTML) {
            isLoading = false;
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }

        // Toast уведомления
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const content = document.getElementById('toastContent');

            const styles = {
                success: 'bg-emerald-50 text-emerald-800 border border-emerald-200',
                error: 'bg-red-50 text-red-800 border border-red-200',
                warning: 'bg-amber-50 text-amber-800 border border-amber-200',
                info: 'bg-indigo-50 text-indigo-800 border border-indigo-200'
            };

            content.className =
                `flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-medium ${styles[type] || styles.info}`;
            content.innerHTML = `<span>${message}</span>`;

            toast.classList.remove('hidden', 'translate-y-4', 'opacity-0');

            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }
        // Toast уведомления
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const content = document.getElementById('toastContent');

            const styles = {
                success: 'bg-emerald-50 text-emerald-800 border border-emerald-200',
                error: 'bg-red-50 text-red-800 border border-red-200',
                warning: 'bg-amber-50 text-amber-800 border border-amber-200',
                info: 'bg-indigo-50 text-indigo-800 border border-indigo-200'
            };

            content.className =
                `flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-medium ${styles[type] || styles.info}`;
            content.innerHTML = `<span>${message}</span>`;

            toast.classList.remove('hidden', 'translate-y-4', 'opacity-0');

            clearTimeout(toast._timeout);
            toast._timeout = setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }

        // Закрытие модального окна по клику вне его
        document.getElementById('completionModal').addEventListener('click', function(e) {
            if (e.target === this) closeTaskModal();
        });

        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('completionModal').classList.contains('hidden')) {
                closeTaskModal();
            }
        });
    </script>

    <style>
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Анимации для модального окна */
        #modalContent {
            transition: transform 0.2s ease-out, opacity 0.2s ease-out;
        }

        /* Улучшенный скроллбар */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endsection
