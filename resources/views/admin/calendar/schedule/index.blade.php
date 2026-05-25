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
                                <a href="{{ route('calendar.schedule.timeline', [
                                    'employee' => $employee->id,
                                    'date' => $date,
                                ]) }}"
                                    class="
                                    block
                                    rounded-2xl
                                    transition`
                                    hover:scale-[1.01]
                                    hover:shadow-md
                                ">  
                                    <div class="flex flex-col">
                                        <span class="text-sm">
                                            {{ \Carbon\Carbon::parse($date)->format('d') }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">
                                            {{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}
                                        </span>
                                    </div>
                                </a>
                              
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
                                            $completedCount = $meta['completed_count'] ?? 0;
                                            $quotaTotal = $meta['quota_total'] ?? 0;
                                            $assignmentId = $day['tasks'][$taskId]['assignment_id'] ?? null;

                                            $percent = $quotaTotal > 0 ? ($completedCount / $quotaTotal) * 100 : 0;
                                        @endphp

                                        @if ($assignmentId)
                                            {{-- КЛИКАБЕЛЬНАЯ ЗАДАЧА --}}
                                            <div onclick="openModal({{ $assignmentId }}, '{{ $date }}')"
                                                class="cursor-pointer px-2 py-1.5 rounded-md text-xs transition hover:bg-blue-100 bg-blue-50 text-blue-700 border border-blue-200"
                                                title="Нажмите, чтобы отметить выполнение">

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

                                                {{-- Прогресс-бар --}}
                                                <div class="mt-1">
                                                    <div class="w-full bg-blue-200 rounded-full h-1">
                                                        <div class="bg-blue-500 h-1 rounded-full"
                                                            style="width: {{ $percent }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
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

    {{-- МОДАЛЬНОЕ ОКНО --}}
    <div id="completionModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
        style="background-color: rgba(0,0,0,0.5);">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm">

            {{-- Заголовок --}}
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Отметка выполнения</h3>
                <button onclick="closeModal()"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            {{-- Тело --}}
            <div class="p-4">
                <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm space-y-1">
                    <div class="font-medium" id="infoTaskName">—</div>
                    <div class="text-gray-600" id="infoEmployee">—</div>
                    <div class="text-gray-600" id="infoDate">—</div>
                    <div class="text-gray-600 mt-1">
                        Квота: <strong id="infoQuota">0</strong> | Время/шт: <strong id="infoTime">0</strong> мин
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Выполнено итераций:</label>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="changeCompleted(-1)"
                            class="w-10 h-10 flex items-center justify-center bg-red-100 text-red-600 rounded-lg hover:bg-red-200 text-xl font-bold">
                            −
                        </button>

                        <input type="number" id="completedInput" min="0" value="0"
                            onchange="validateCompleted()"
                            class="flex-1 text-center text-lg font-bold border-2 border-gray-300 rounded-lg py-2 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">

                        <button type="button" onclick="changeCompleted(1)"
                            class="w-10 h-10 flex items-center justify-center bg-green-100 text-green-600 rounded-lg hover:bg-green-200 text-xl font-bold">
                            +
                        </button>
                    </div>

                    <div class="mt-1 text-xs text-gray-500">
                        Максимум: <span id="maxQuota">0</span>
                    </div>

                    <div id="quickCalc" class="mt-3 p-2 bg-indigo-50 rounded text-xs text-indigo-800">
                        Выполнено: <strong id="calcDone">0</strong> мин | Осталось: <strong id="calcRemaining">0</strong>
                        мин
                    </div>
                </div>
            </div>

            {{-- Футер --}}
            <div class="flex items-center justify-between p-4 border-t bg-gray-50 rounded-b-lg">
                <a href="#" id="assignmentLink" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    📋 К назначению
                </a>

                <div class="flex gap-2">
                    <button onclick="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Отмена
                    </button>
                    <button onclick="saveCompletion()"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        💾 Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>






    <script>
        let currentAssignmentId = null;
        let currentQuota = 0;
        let currentIterationTime = 0;

        function openModal(assignmentId, date) {
            fetch(`/calendar/schedule/assignment-info/${assignmentId}`)
                .then(res => res.json())
                .then(data => {
                    currentAssignmentId = data.assignment_id;
                    currentQuota = data.quota;
                    currentIterationTime = data.iteration_time;

                    document.getElementById('infoTaskName').textContent = data.task_name;
                    document.getElementById('infoEmployee').textContent = '👤 ' + data.employee_name;
                    document.getElementById('infoDate').textContent = '📅 ' + date;
                    document.getElementById('infoQuota').textContent = data.quota;
                    document.getElementById('infoTime').textContent = data.iteration_time;

                    const input = document.getElementById('completedInput');
                    input.value = data.completed_count;
                    input.max = data.quota;

                    document.getElementById('maxQuota').textContent = data.quota;
                    document.getElementById('assignmentLink').href =
                        `/calendar/tasks/${data.task_id}/assignments/${assignmentId}/edit`;

                    updateCalculation();

                    document.getElementById('completionModal').classList.remove('hidden');
                    document.getElementById('completionModal').classList.add('flex');
                });
        }

        function closeModal() {
            document.getElementById('completionModal').classList.add('hidden');
            document.getElementById('completionModal').classList.remove('flex');
        }

        function changeCompleted(delta) {
            const input = document.getElementById('completedInput');
            let val = parseInt(input.value || 0) + delta;
            input.value = Math.max(0, Math.min(val, currentQuota));
            updateCalculation();
        }

        function validateCompleted() {
            const input = document.getElementById('completedInput');
            let val = parseInt(input.value || 0);
            input.value = Math.max(0, Math.min(val, currentQuota));
            updateCalculation();
        }

        function updateCalculation() {
            const completed = parseInt(document.getElementById('completedInput').value || 0);
            const remaining = currentQuota - completed;
            document.getElementById('calcDone').textContent = completed * currentIterationTime;
            document.getElementById('calcRemaining').textContent = remaining * currentIterationTime;
        }

        function saveCompletion() {
            if (!currentAssignmentId) return;

            fetch(`/calendar/schedule/complete/${currentAssignmentId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        completed_count: parseInt(document.getElementById('completedInput').value)
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeModal();
                        location.reload();
                    }
                });
        }

        document.getElementById('completionModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
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
    </style>
@endsection
