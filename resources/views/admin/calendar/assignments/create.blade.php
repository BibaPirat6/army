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

    <form action="{{ isset($assignment) && $assignment->exists ? route('calendar.assignments.update', [$task->id, $assignment->id]) : route('calendar.assignments.store', $task->id) }}" method="POST">
        @csrf
        @if(isset($assignment) && $assignment->exists)
            @method('PUT')
        @else
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
        @endif

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Приоритет</label>
                <input type="number" name="priority" value="{{ $assignment->priority ?? 1 }}" min="1"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Квота</label>
                <input type="number" name="quota" value="{{ $assignment->quota ?? 1 }}" min="1"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Начало</label>
                <input type="date" name="start_date"
                    value="{{ isset($assignment) && $assignment->start_date ? $assignment->start_date->format('Y-m-d') : ($task->start_date ? $task->start_date->format('Y-m-d') : '') }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Окончание</label>
                <input type="date" name="end_date"
                    value="{{ isset($assignment) && $assignment->end_date ? $assignment->end_date->format('Y-m-d') : ($task->end_date ? $task->end_date->format('Y-m-d') : '') }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ url()->previous() }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Отмена
            </a>
            <button type="submit" class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                {{ isset($assignment) && $assignment->exists ? 'Обновить' : 'Назначить' }}
            </button>
        </div>
    </form>
</div>
@endsection