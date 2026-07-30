@extends('layouts.main')

@section('header-title', 'Новое назначение')

@section('content')
<div class="max-w-lg mx-auto px-4 py-6">
    <div class="mb-4">
        <a href="{{ route('calendar.matrix.index', $commissariatId) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 text-sm">
            ← Назад к матрице
        </a>
    </div>

    <h2 class="text-lg font-semibold text-gray-800 mb-4">Новое назначение</h2>

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

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('calendar.assignments.store', $task->id) }}" method="POST">
        @csrf
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Приоритет (1-10)</label>
                <input type="number" name="priority" value="{{ old('priority', 1) }}" min="1" max="10"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Квота (макс. {{ $availableQuota }})
                </label>
                <input type="number" name="quota" value="{{ old('quota', 1) }}" min="1" max="{{ $availableQuota }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('calendar.matrix.index', $commissariatId) }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Отмена
            </a>
            <button type="submit" class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Назначить
            </button>
        </div>
    </form>
</div>
@endsection