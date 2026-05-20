@extends('layouts.main')

@section('header-title')
    Создание задачи
@endsection

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к календарю
            </a>
        </div>
        
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Новая задача</h1>
            
            <form action="{{ route('calendar.tasks.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                    <input type="text" name="title" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                    <textarea name="description" rows="2"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ответственный</label>
                    <select name="employee_position_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Не назначен</option>
                        @foreach($employeePositions as $ep)
                            <option value="{{ $ep->id }}">
                                {{ $ep->employee?->person?->фамилия }} {{ $ep->employee?->person?->имя }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
                        <input type="color" name="color" value="#3788d8"
                            class="h-10 w-full rounded-lg border-gray-300 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Квота</label>
                        <input type="number" name="quota" min="1" value="1"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала *</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания *</label>
                        <input type="date" name="end_date" value="{{ $startDate }}" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                
                {{-- Файлы --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Файлы</label>
                    <input type="file" name="files[]" multiple
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-400 mt-1">Максимум 10 МБ на файл</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <a href="{{ route('calendar.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Отмена</a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
@endsection