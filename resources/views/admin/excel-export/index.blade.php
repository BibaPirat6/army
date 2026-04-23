@extends('layouts.main')

@section('header-title')
    Экспорт в Excel
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Заголовок секции -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Выберите данные для экспорта</h2>
            <p class="mt-2 text-sm text-gray-600">Экспорт данных в формате Excel (.xlsx) для дальнейшего анализа и отчетности</p>
        </div>

        <!-- Сетка карточек экспорта -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            
            <!-- Карточка: Сотрудники -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Сотрудники</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Полный список сотрудников с личными данными, контактной информацией, учетными записями и ролями.
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">~ всех сотрудников</span>
                        <a href="{{ route('excel-export.employee') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Экспорт
                        </a>
                    </div>
                </div>
            </div>

            <!-- Карточка: Отделы (пример) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Отделы</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Структура компании, список отделов, количество сотрудников и руководители.
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">~ в разработке</span>
                        <button disabled 
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-500 uppercase tracking-widest cursor-not-allowed transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Скоро
                        </button>
                    </div>
                </div>
            </div>

            <!-- Карточка: Зарплаты (пример) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Зарплаты</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Данные о заработной плате, начислениях, удержаниях и выплатах за период.
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">~ в разработке</span>
                        <button disabled 
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-500 uppercase tracking-widest cursor-not-allowed transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Скоро
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection