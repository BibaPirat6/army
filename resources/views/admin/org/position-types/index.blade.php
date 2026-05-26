@extends('layouts.main')

@section('header-title')
    Типы должностей
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif


    <form method="GET" class="flex gap-4 mb-6">

        <input type="text" name="search" value="{{ $filters->search }}" placeholder="Поиск" class="border rounded px-3 py-2">

        <select name="sort_by" class="border rounded px-3 py-2">
            <option value="id">ID</option>
            <option value="name">Название</option>
            <option value="created_at">Дата создания</option>
        </select>

        <select name="sort_direction" class="border rounded px-3 py-2">
            <option value="desc">DESC</option>
            <option value="asc">ASC</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-black text-white rounded">
            Применить
        </button>

        <a href="{{ route('positions.index') }}" class="px-4 py-2 border rounded">
            Сбросить
        </a>
    </form>




    <div class="w-full p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#060606]">Типы должностей</h1>
                <p class="text-[#565A5B] mt-1">Список всех типов должностей</p>
            </div>
            <a href="{{ route('position-types.create', [
                'back_url' => url()->full(),
            ]) }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать тип должности
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
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($positionTypes as $type)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-6 py-4 text-[#060606] font-medium">{{ $type->id }}</td>
                                <td class="px-6 py-4 text-[#060606]">{{ $type->name }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2.5">
                                        <!-- Подробнее -->
                                        <a href="{{ route('position-types.show', [
                                            'id' => $type->id,
                                            'back_url' => url()->full(),
                                        ]) }}"
                                            class="inline-flex items-center px-3.5 py-1.5 bg-[#746c6f] text-white text-sm font-medium rounded-lg hover:bg-[#746c6f]/85 transition-colors duration-200 shadow-sm hover:shadow">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Подробнее
                                        </a>

                                        <!-- Редактировать -->
                                        <a href="{{ route('position-types.edit', [
                                            'id' => $type->id,
                                            'back_url' => url()->full(),
                                        ]) }}"
                                            class="inline-flex items-center px-3.5 py-1.5 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/85 transition-colors duration-200 shadow-sm hover:shadow">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Редактировать
                                        </a>

                                        <!-- Удалить -->
                                        <form
                                            action="{{ route('position-types.delete', [
                                                'id' => $type->id,
                                                'back_url' => url()->full(),
                                            ]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Вы уверены, что хотите удалить тип должности «{{ $type->name }}»?');"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3.5 py-1.5 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/85 transition-colors duration-200 shadow-sm hover:shadow">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-lg font-medium">Нет типов должностей</p>
                                        <p class="text-[#7F7F7F] mt-1">Создайте первый тип должности для начала работы</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('includes.pagination', ['paginator' => $positionTypes])
    </div>
@endsection
