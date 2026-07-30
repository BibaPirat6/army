@extends('layouts.main')

@section('header-title')
    Редактирование подзадачи
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

        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Редактирование подзадачи</h1>
            <p class="text-sm text-gray-500 mb-6">Задача: <span class="font-medium text-indigo-600">{{ $task->title }}</span></p>

            <form action="{{ route('calendar.tasks.subtasks.update', [$task, $subtask]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                    <input type="text" name="title" value="{{ old('title', $subtask->title) }}" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Минимум (мин) *</label>
                        <input type="number" name="min_time_minutes" value="{{ old('min_time_minutes', $subtask->min_time_minutes) }}" required min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('min_time_minutes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Среднее (мин) *</label>
                        <input type="number" name="avg_time_minutes" value="{{ old('avg_time_minutes', $subtask->avg_time_minutes) }}" required min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('avg_time_minutes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Максимум (мин) *</label>
                        <input type="number" name="max_time_minutes" value="{{ old('max_time_minutes', $subtask->max_time_minutes) }}" required min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('max_time_minutes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('calendar.tasks.subtasks.index', $task) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Отмена</a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endsection