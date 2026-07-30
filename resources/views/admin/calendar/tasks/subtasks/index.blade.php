@extends('layouts.main')

@section('header-title')
    Подзадачи задачи: {{ $task->title }}
@endsection

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('calendar.tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к задаче
            </a>
            <a href="{{ route('calendar.tasks.subtasks.create', $task) }}" 
                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Добавить подзадачу
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">Подзадачи</h1>
                <p class="text-sm text-gray-500 mt-1">Задача: {{ $task->title }}</p>
            </div>

            @if(session('success'))
                <div class="m-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($subtasks->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Мин (мин)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Сред (мин)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Макс (мин)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($subtasks as $index => $subtask)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-800">{{ $subtask->title }}</td>
                                    <td class="px-6 py-4 text-center text-sm">{{ $subtask->min_time_minutes }}</td>
                                    <td class="px-6 py-4 text-center text-sm font-semibold text-indigo-600">{{ $subtask->avg_time_minutes }}</td>
                                    <td class="px-6 py-4 text-center text-sm">{{ $subtask->max_time_minutes }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('calendar.tasks.subtasks.edit', [$task, $subtask]) }}" 
                                                class="text-amber-600 hover:text-amber-800 p-1 rounded-lg hover:bg-amber-50 transition"
                                                title="Редактировать">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('calendar.tasks.subtasks.destroy', [$task, $subtask]) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Удалить подзадачу?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 p-1 rounded-lg hover:bg-red-50 transition"
                                                    title="Удалить">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-sm font-semibold text-gray-700">Итого:</td>
                                <td class="px-6 py-3 text-center text-sm font-semibold">{{ $subtasks->sum('min_time_minutes') }}</td>
                                <td class="px-6 py-3 text-center text-sm font-semibold text-indigo-700">{{ $subtasks->sum('avg_time_minutes') }}</td>
                                <td class="px-6 py-3 text-center text-sm font-semibold">{{ $subtasks->sum('max_time_minutes') }}</td>
                                <td class="px-6 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500">Нет подзадач</p>
                    <a href="{{ route('calendar.tasks.subtasks.create', $task) }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                        + Добавить первую подзадачу
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection