@extends('layouts.main')

@section('header-title')
    Задача: {{ $task->title }}
@endsection

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Назад к календарю
            </a>
            <div class="flex gap-3">
                <a href="{{ route('calendar.tasks.edit', $task) }}"
                    class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Редактировать
                </a>
                <button onclick="deleteTask()"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Удалить
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Заголовок с цветом --}}
            <div class="px-6 py-4"
                style="background-color: {{ $task->color }}20; border-left: 4px solid {{ $task->color }}">
                <h1 class="text-2xl font-bold text-gray-800">{{ $task->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Создана: {{ $task->created_at->format('d.m.Y H:i') }}</p>
            </div>

            <div class="p-6 space-y-6">
                {{-- Описание --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Описание</h3>
                    <div class="mt-2 text-gray-700 whitespace-pre-wrap">
                        {{ $task->description ?: 'Нет описания' }}
                    </div>
                </div>

                {{-- Основная информация --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Дата начала</h3>
                        <p class="mt-1 text-gray-800 font-medium">{{ $task->start_date->format('d.m.Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Дата окончания</h3>
                        <p class="mt-1 text-gray-800 font-medium">{{ $task->end_date->format('d.m.Y') }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Квота</h3>
                        <p class="mt-1 text-gray-800 font-medium">{{ $task->quota ?: 'Не указана' }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Цвет</h3>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full border shadow-sm" style="background-color: {{ $task->color }}">
                            </div>
                            <span class="text-gray-800 font-mono text-sm">{{ $task->color }}</span>
                        </div>
                    </div>
                </div>

                {{-- Ответственный --}}
                @if($task->employeePosition)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Ответственный</h3>
                        <div class="mt-2">
                            @php
                                $person = $task->employeePosition->employee?->person;
                                $cp = $task->employeePosition->commissariatPosition;
                                $unitName = $cp->division?->name ?? $cp->department?->name ?? $cp->commissariat?->name ?? '';
                            @endphp
                            <p class="font-semibold text-gray-800 text-lg">
                                {{ $person ? trim($person->фамилия . ' ' . $person->имя . ' ' . ($person->отчество ?? '')) : 'Не указан' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ $cp->position?->name ?? '' }} — {{ $unitName }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- БЛОКИ ВРЕМЕНИ --}}
                @php
                    function formatTime($minutes)
                    {
                        if ($minutes == 0)
                            return '0 мин';
                        $hours = floor($minutes / 60);
                        $mins = $minutes % 60;
                        if ($hours == 0)
                            return "{$mins} мин";
                        if ($mins == 0)
                            return "{$hours} ч";
                        return "{$hours} ч {$mins} мин";
                    }
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Блок 1: На 1 выполнение --}}
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">⏱️</span>
                            <h3 class="font-semibold text-blue-800">На 1 выполнение</h3>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center border-b border-blue-200 pb-1">
                                <span class="text-gray-600">Минимум:</span>
                                <span class="font-bold text-blue-700 text-lg">{{ formatTime($totalMin) }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-blue-200 pb-1">
                                <span class="text-gray-600">Среднее:</span>
                                <span class="font-bold text-blue-700 text-lg">{{ formatTime($totalAvg) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Максимум:</span>
                                <span class="font-bold text-blue-700 text-lg">{{ formatTime($totalMax) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Блок 2: На всю квоту --}}
                    <div
                        class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-5 border border-indigo-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">📦</span>
                            <h3 class="font-semibold text-indigo-800">На всю квоту</h3>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center border-b border-indigo-200 pb-1">
                                <span class="text-gray-600">Минимум:</span>
                                <span
                                    class="font-bold text-indigo-700 text-lg">{{ formatTime($totalMin * ($task->quota ?? 1)) }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-indigo-200 pb-1">
                                <span class="text-gray-600">Среднее:</span>
                                <span
                                    class="font-bold text-indigo-700 text-lg">{{ formatTime($totalAvg * ($task->quota ?? 1)) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Максимум:</span>
                                <span
                                    class="font-bold text-indigo-700 text-lg">{{ formatTime($totalMax * ($task->quota ?? 1)) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 text-xs text-indigo-600">
                            Квота задачи: {{ $task->quota ?? 1 }} итераций
                        </div>
                    </div>

                    {{-- Блок 3: Выполнено сотрудниками --}}
                    <div
                        class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border border-emerald-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-2xl">✅</span>
                            <h3 class="font-semibold text-emerald-800">Выполнено</h3>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center border-b border-emerald-200 pb-1">
                                <span class="text-gray-600">Итераций:</span>
                                <span class="font-bold text-emerald-700 text-lg">{{ $totalCompleted }} /
                                    {{ $totalQuotaAssigned ?: 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-emerald-200 pb-1">
                                <span class="text-gray-600">Времени (сред):</span>
                                <span
                                    class="font-bold text-emerald-700 text-lg">{{ formatTime($totalCompleted * $totalAvg) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Прогресс:</span>
                                <span
                                    class="font-bold text-emerald-700 text-lg">{{ $totalQuotaAssigned > 0 ? round(($totalCompleted / $totalQuotaAssigned) * 100) : 0 }}%</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="w-full bg-emerald-200 rounded-full h-2.5">
                                <div class="bg-emerald-500 h-2.5 rounded-full transition-all"
                                    style="width: {{ $totalQuotaAssigned > 0 ? ($totalCompleted / $totalQuotaAssigned) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ПОДЗАДАЧИ --}}
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Подзадачи</h3>
                            <p class="text-sm text-gray-500">Декомпозиция задачи на этапы</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                Всего: {{ $subtasks->count() }}
                            </div>
                            <a href="{{ route('calendar.tasks.subtasks.index', $task) }}"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Управление подзадачами
                            </a>
                        </div>
                    </div>

                    @if($subtasks->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white rounded-xl overflow-hidden">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Название</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Мин</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Сред</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Макс</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            % от задачи</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($subtasks as $index => $subtask)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 font-medium text-gray-800">{{ $subtask->title }}</td>
                                            <td class="px-4 py-3 text-center text-sm">{{ $subtask->min_time_minutes }} мин</td>
                                            <td class="px-4 py-3 text-center text-sm font-semibold text-indigo-600">
                                                {{ $subtask->avg_time_minutes }} мин
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm">{{ $subtask->max_time_minutes }} мин</td>
                                            <td class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                                        <div class="bg-indigo-500 h-1.5 rounded-full"
                                                            style="width: {{ $totalAvg > 0 ? ($subtask->avg_time_minutes / $totalAvg) * 100 : 0 }}%">
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500">{{ round(($subtask->avg_time_minutes / max($totalAvg, 1)) * 100) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 font-semibold">
                                    <tr>
                                        <td colspan="2" class="px-4 py-3 text-sm text-gray-700">Итого:</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $totalMin }} мин</td>
                                        <td class="px-4 py-3 text-center text-sm text-indigo-700">{{ $totalAvg }} мин</td>
                                        <td class="px-4 py-3 text-center text-sm">{{ $totalMax }} мин</td>
                                        <td class="px-4 py-3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-3 text-right">
                            <a href="{{ route('calendar.tasks.subtasks.index', $task) }}"
                                class="text-sm text-indigo-600 hover:text-indigo-800">
                                Полный список подзадач →
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-gray-500">Нет подзадач</p>
                            <a href="{{ route('calendar.tasks.subtasks.create', $task) }}"
                                class="mt-2 inline-block text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                + Добавить подзадачу
                            </a>
                        </div>
                    @endif
                </div>

               {{-- Файлы --}}
@php
    $taskFiles = $task->getFilesList();
@endphp

@if(count($taskFiles) > 0)
    <div>
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Файлы ({{ count($taskFiles) }})</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($taskFiles as $file)
                @if(isset($file['id']) && isset($file['path']))
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">📄</span>
                            <div>
                                @php
                                    $filePath = 'storage/' . ltrim($file['path'], '/');
                                    $fileName = $file['original_name'] ?? 'Файл';
                                    $fileSize = isset($file['size']) ? round($file['size'] / 1024, 1) : 0;
                                @endphp
                                @if(file_exists(public_path($filePath)))
                                    <a href="{{ asset($filePath) }}" target="_blank" 
                                        class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                        {{ $fileName }}
                                    </a>
                                @else
                                    <span class="text-gray-400 font-medium text-sm">
                                        {{ $fileName }}
                                        <span class="text-xs text-red-400">(файл не найден)</span>
                                    </span>
                                @endif
                                <p class="text-xs text-gray-400">{{ $fileSize }} КБ</p>
                            </div>
                        </div>
                        <form action="{{ route('calendar.tasks.files.delete', [$task->id, $file['id']]) }}" 
                              method="POST" 
                              style="display: inline;"
                              onsubmit="return confirm('Удалить файл?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" style="background: none; border: none; cursor: pointer;">
                                🗑️
                            </button>
                        </form>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

                {{-- Назначенные сотрудники --}}
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Назначенные сотрудники</h3>
                        <a href="{{ route('calendar.matrix.index', $task->employeePosition?->commissariatPosition?->commissariat_id ?? 1) }}"
                            class="px-3 py-1 text-sm text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
                            + Назначить
                        </a>
                    </div>

                    @if($task->taskAssignments->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($task->taskAssignments as $assignment)
                                @php
                                    $person = $assignment->employee->person;
                                    $name = $person ? trim($person->фамилия . ' ' . mb_substr($person->имя, 0, 1) . '.') : '#' . $assignment->employee_id;
                                    $percent = $assignment->quota ? round($assignment->completed_count / $assignment->quota * 100) : 0;
                                @endphp
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                                {{ mb_substr($name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('employees.show', $assignment->employee_id) }}"
                                                    class="font-semibold text-gray-800 hover:text-indigo-600">
                                                    {{ $name }}
                                                </a>
                                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5">
                                                    <span>Приоритет: P{{ $assignment->priority }}</span>
                                                    <span>Квота: {{ $assignment->quota }}</span>
                                                    <span>Выполнено: {{ $assignment->completed_count }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div class="w-32">
                                                <div class="flex justify-between text-xs mb-1">
                                                    <span class="text-gray-500">Прогресс</span>
                                                    <span class="font-medium">{{ $percent }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="h-2 rounded-full {{ $percent >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}"
                                                        style="width: {{ min($percent, 100) }}%"></div>
                                                </div>
                                            </div>
                                            <a href="{{ route('calendar.assignments.edit', [$task->id, $assignment->id]) }}"
                                                class="text-amber-600 hover:text-amber-800">✏️</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400 bg-gray-50 rounded-xl border-2 border-dashed">
                            Нет назначенных сотрудников
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const taskId = {{ $task->id }};
    const csrfToken = '{{ csrf_token() }}';

    function deleteTask() {
        if (!confirm('Вы уверены, что хотите удалить эту задачу? Это действие нельзя отменить.')) return;

        // Создаем форму для DELETE запроса
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/calendar/tasks/${taskId}`;
        form.style.display = 'none';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endpush