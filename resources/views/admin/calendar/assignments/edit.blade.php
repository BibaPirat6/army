@extends('layouts.main')

@section('header-title', 'Редактировать назначение')

@section('content')
<div class="max-w-lg mx-auto px-4 py-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Редактировать назначение</h2>

    {{-- Задача --}}
    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
        <div class="text-sm text-gray-500">Задача</div>
        <a href="{{ route('calendar.tasks.show', $task->id) }}" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
            {{ $task->title }}
        </a>
        <div class="text-xs text-gray-400 mt-0.5">
            {{ $task->start_date ? $task->start_date->format('d.m.Y') : 'без даты' }}
            — {{ $task->end_date ? $task->end_date->format('d.m.Y') : 'без срока' }}
        </div>
        <div class="text-xs text-gray-400 mt-0.5">
            Квота задачи: <strong>{{ $task->quota ?? 'не указана' }}</strong>
            <span class="ml-1 {{ $availableQuota <= 0 ? 'text-red-500' : 'text-gray-400' }}">
                (доступно: {{ $availableQuota }})
            </span>
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
        <a href="{{ route('employees.show', $employee->id) }}" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">{{ $name }}</a>
        @if($cp)
            <div class="text-xs text-gray-400 mt-0.5">
                <a href="{{ route('commissariat-positions.show', array_filter(['id' => $cp->id, 'back_url' => url()->full(), 'commissariat_id' => $cp->commissariat_id, 'employeeId' => $employee->id])) }}"
                   class="text-gray-500 hover:text-indigo-600 transition">{{ $cp->position?->name }}</a>
            </div>
        @endif
    </div>

    {{-- Кнопка удаления --}}
    <div class="flex justify-end mb-4">
        <form action="{{ route('calendar.assignments.destroy', [$task->id, $assignment->id]) }}" method="POST"
            onsubmit="return confirm('Удалить назначение этого сотрудника на задачу?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition">
                🗑️ Удалить назначение
            </button>
        </form>
    </div>

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('calendar.assignments.update', [$task->id, $assignment->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Приоритет (1-10)</label>
                <input type="number" name="priority" value="{{ old('priority', $assignment->priority) }}" min="1" max="10"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Квота (макс. {{ $availableQuota + $assignment->quota }})
                </label>
                <input type="number" name="quota" value="{{ old('quota', $assignment->quota) }}" min="1" max="{{ $availableQuota + $assignment->quota }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Начало</label>
                <input type="date" name="start_date" value="{{ old('start_date', $assignment->start_date?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Окончание</label>
                <input type="date" name="end_date" value="{{ old('end_date', $assignment->end_date?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('calendar.matrix.index', $commissariatId) }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Отмена
            </a>
            <button type="submit" class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Обновить
            </button>
        </div>
    </form>
</div>
@endsection