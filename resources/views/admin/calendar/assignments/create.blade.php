@extends('layouts.main')

@section('header-title', isset($assignment) ? 'Редактировать назначение' : 'Новое назначение')

@section('content')
<div class="max-w-lg mx-auto px-4 py-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">
        {{ isset($assignment) && $assignment->exists ? 'Редактировать назначение' : 'Новое назначение' }}
    </h2>

    {{-- Задача --}}
    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
        <div class="text-sm text-gray-500">Задача</div>
        <a href="{{ route('calendar.show', $task->id) }}" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
            {{ $task->title }}
        </a>
        <div class="text-xs text-gray-400 mt-0.5">
            {{ $task->start_date ? $task->start_date->format('d.m.Y') : 'без даты' }}
            —
            {{ $task->end_date ? $task->end_date->format('d.m.Y') : 'без срока' }}
        </div>
        <div class="text-xs text-gray-400 mt-0.5">
            Квота задачи: <strong>{{ $task->quota ?? 'не указана' }}</strong>
            @if($task->quota)
                @php
                    $usedQuota = $task->taskAssignments
                        ->where('employee_id', '!=', ($assignment->employee_id ?? 0))
                        ->sum('quota');
                    $availableQuota = $task->quota - $usedQuota;
                @endphp
                <span class="ml-1 {{ $availableQuota <= 0 ? 'text-red-500' : 'text-gray-400' }}">
                    (доступно: {{ max(0, $availableQuota) }})
                </span>
            @endif
        </div>
    </div>

    {{-- Сотрудник --}}
    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
        <div class="text-sm text-gray-500">Сотрудник</div>
        @php
            $p = $employee->person;
            $name = $p ? trim($p->фамилия.' '.$p->имя.' '.($p->отчество??'')) : '#'.$employee->id;
            $ep = $employee->employeePositions->first();
            $cp = $ep?->commissariatPosition;
        @endphp
        <a href="{{ route('employees.show', $employee->id) }}" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
            {{ $name }}
        </a>
        @if($cp)
            <div class="text-xs text-gray-400 mt-0.5">
                <a href="{{ route('commissariat-positions.show', array_filter(['id' => $cp->id, 'back_url' => url()->full(), 'commissariat_id' => $cp->commissariat_id, 'employeeId' => $employee->id])) }}"
                   class="text-gray-500 hover:text-indigo-600 transition">
                    {{ $cp->position?->name }}
                </a>
            </div>
        @endif
    </div>

    <form id="assignmentForm"
          action="{{ isset($assignment) && $assignment->exists ? route('calendar.assignments.update', [$task->id, $assignment->id]) : route('calendar.assignments.store', $task->id) }}"
          method="POST">
        @csrf
        @if(isset($assignment) && $assignment->exists)
            @method('PUT')
        @else
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
        @endif

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Приоритет</label>
                <input type="number" name="priority" id="priority" value="{{ $assignment->priority ?? 1 }}" min="1"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Квота
                    <span class="text-xs text-gray-400 font-normal">(макс. <span id="maxQuota">{{ $availableQuota + ($assignment->quota ?? 0) }}</span>)</span>
                </label>
                <input type="number" name="quota" id="quota"
                    value="{{ $assignment->quota ?? 1 }}"
                    min="1"
                    max="{{ $availableQuota + ($assignment->quota ?? 0) }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    data-original="{{ $assignment->quota ?? 0 }}">
            </div>
        </div>

        {{-- Ошибка квоты --}}
        <div id="quotaError" class="hidden mb-4 p-2 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600"></div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Начало</label>
                <input type="date" name="start_date" id="start_date"
                    value="{{ isset($assignment) && $assignment->start_date ? $assignment->start_date->format('Y-m-d') : ($task->start_date ? $task->start_date->format('Y-m-d') : '') }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Окончание</label>
                <input type="date" name="end_date" id="end_date"
                    value="{{ isset($assignment) && $assignment->end_date ? $assignment->end_date->format('Y-m-d') : ($task->end_date ? $task->end_date->format('Y-m-d') : '') }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        {{-- Ошибка дат --}}
        <div id="dateError" class="hidden mb-4 p-2 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600"></div>

        <div class="flex justify-end gap-3">
            <a href="{{ url()->previous() }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Отмена
            </a>
            <button type="submit" id="submitBtn" class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                {{ isset($assignment) && $assignment->exists ? 'Обновить' : 'Назначить' }}
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const form = document.getElementById('assignmentForm');
    const quotaInput = document.getElementById('quota');
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const quotaError = document.getElementById('quotaError');
    const dateError = document.getElementById('dateError');
    const submitBtn = document.getElementById('submitBtn');

    const maxQuota = parseInt(document.getElementById('maxQuota').textContent) || 9999;
    const taskStart = '{{ $task->start_date?->format("Y-m-d") }}';
    const taskEnd = '{{ $task->end_date?->format("Y-m-d") }}';

    function validate() {
        let valid = true;
        quotaError.classList.add('hidden');
        dateError.classList.add('hidden');

        // Проверка квоты
        const quota = parseInt(quotaInput.value) || 0;
        if (quota > maxQuota) {
            quotaError.textContent = `Квота не может превышать доступную (${maxQuota}). Остальные сотрудники уже заняли часть квоты.`;
            quotaError.classList.remove('hidden');
            valid = false;
        }
        if (quota < 1) {
            quotaError.textContent = 'Квота должна быть не менее 1.';
            quotaError.classList.remove('hidden');
            valid = false;
        }

        // Проверка дат
        const start = startInput.value;
        const end = endInput.value;
        if (start && end && end < start) {
            dateError.textContent = 'Дата окончания не может быть раньше даты начала.';
            dateError.classList.remove('hidden');
            valid = false;
        }
        if (taskStart && start && start < taskStart) {
            dateError.textContent = 'Дата начала не может быть раньше даты начала задачи (' + taskStart + ').';
            dateError.classList.remove('hidden');
            valid = false;
        }
        if (taskEnd && end && end > taskEnd) {
            dateError.textContent = 'Дата окончания не может быть позже даты окончания задачи (' + taskEnd + ').';
            dateError.classList.remove('hidden');
            valid = false;
        }

        submitBtn.disabled = !valid;
        if (!valid) {
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        return valid;
    }

    quotaInput.addEventListener('input', validate);
    startInput.addEventListener('input', validate);
    endInput.addEventListener('input', validate);

    form.addEventListener('submit', function (e) {
        if (!validate()) {
            e.preventDefault();
        }
    });

    // Начальная проверка
    validate();
})();
</script>
@endsection