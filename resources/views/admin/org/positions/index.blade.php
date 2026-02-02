@extends('layouts.main')

@section('header-title')
    Должности
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif


    <div class="max-w-4xl p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#060606]">Должности</h1>
                <p class="text-[#565A5B] mt-1">Список всех должностей</p>
            </div>
            <a href="{{ route('positions.create') }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать должность
            </a>
        </div>

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Название</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Тип должности</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($positions as $position)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-6 py-4 text-[#060606] font-medium">{{ $position->id }}</td>
                                <td class="px-6 py-4 text-[#060606]">{{ $position->name }}</td>
                                <td class="px-6 py-4 text-[#060606]">{{ $position->positionType->name ?? "*"}}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('positions.show', $position->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-[#746c6f] text-white text-sm font-medium rounded-lg hover:bg-[#746ccc]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Подробнее
                                    </a>
                                    <a href="{{ route('positions.edit', $position->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Редактировать
                                    </a>
                                    <form class="mt-0.5" action="{{ route('positions.delete', $position->id) }}"
                                        method="POST" class="inline-block"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить должность \"{{ $position->name }}\"?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-lg font-medium">Нет должностей</p>
                                        <p class="text-[#7F7F7F] mt-1">Создайте первую должность для начала работы</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        @include('includes.pagination', ['paginator' => $positions])
    </div>
@endsection
