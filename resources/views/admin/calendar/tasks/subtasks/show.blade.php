@extends('layouts.main')

@section('header-title')
    Подзадача: {{ $subtask->title }}
@endsection

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <a href="{{ route('calendar.tasks.subtasks.index', $task) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к подзадачам
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">{{ $subtask->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Задача: {{ $task->title }}</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-blue-700">{{ $subtask->min_time_minutes }}</div>
                        <div class="text-sm text-gray-600">Минимум (мин)</div>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-indigo-700">{{ $subtask->avg_time_minutes }}</div>
                        <div class="text-sm text-gray-600">Среднее (мин)</div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-purple-700">{{ $subtask->max_time_minutes }}</div>
                        <div class="text-sm text-gray-600">Максимум (мин)</div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('calendar.tasks.subtasks.edit', [$task, $subtask]) }}"
                        class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition">
                        Редактировать
                    </a>
                    <form action="{{ route('calendar.tasks.subtasks.destroy', [$task, $subtask]) }}" method="POST" class="inline"
                        onsubmit="return confirm('Удалить подзадачу?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection