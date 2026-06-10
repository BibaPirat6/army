@extends('layouts.main')

@section('header-title')
    Отделения
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <div class="w-full p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#060606]">Отделения</h1>
                <p class="text-[#565A5B] mt-1">Список всех отделений</p>
            </div>
            <a href="{{ route('divisions.create', [
                'back_url' => url()->full(),
            ]) }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Добавить отделение
            </a>
        </div>




        <form method="GET"
            class="flex flex-wrap items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100">

            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" name="search" value="{{ $filters->search }}" placeholder="Поиск отделения..."
                    class="w-full pl-9 pr-3 py-2 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black focus:border-black outline-none transition">
            </div>

            <div class="w-56">
                <select id="commissariat_id" name="commissariat_id"
                    class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg outline-none transition bg-white">
                    <option value="">Все комиссариаты</option>
                    @foreach ($commissariats as $item)
                        <option value="{{ $item->id }}" @selected($filters->commissariatId == $item->id)>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-56">
                <select id="department_id" name="department_id"
                    class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg outline-none transition bg-white">
                    <option value="">Все отделы</option>
                    @foreach ($departments as $item)
                        <option value="{{ $item->id }}" @selected($filters->departmentId == $item->id)>
                            {{ $item->name }} &larr; {{ $item->commissariat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 ml-auto">
                <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-black transition shadow-sm">
                    Применить
                </button>
                <a href="{{ route('divisions.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Сбросить
                </a>
            </div>

        </form>



        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Название</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Начальник</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Отдел</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Комиссариат</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($divisions as $division)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-6 py-4 text-[#060606] font-medium">{{ $division->id }}</td>


                                <td class="px-6 py-4 text-[#060606]">
                                    <a
                                        href="{{ route('divisions.show', [
                                            'id' => $division->id,
                                            'back_url' => url()->full(),
                                        ]) }}">{{ $division->name }}</a>
                                </td>

                                <td class="px-6 py-4">
                                    @php
                                        $chief = $division->getChiefAttribute();
                                    @endphp
                                    @if ($chief)
                                        <a
                                            href="{{ route('employees.show', [
                                                'id' => $chief->id,
                                                'back_url' => url()->full(),
                                            ]) }}">
                                            {{ $chief->getFullNameAttribute() }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    @if ($division->department)
                                        <a
                                            href="{{ route('departments.show', [
                                                'id' => $division->department_id,
                                                'back_url' => url()->full(),
                                            ]) }}">
                                            {{ $division->department->name }}
                                        </a>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <a
                                        href="{{ route('commissariats.show', [
                                            'id' => $division->commissariat->id,
                                            'back_url' => url()->full(),
                                        ]) }}">{{ $division->commissariat->name }}</a>

                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('divisions.show', [
                                        'id' => $division->id,
                                        'back_url' => url()->full(),
                                    ]) }}"
                                        class="inline-flex items-center px-4 py-2 bg-[#746c6f] text-white text-sm font-medium rounded-lg hover:bg-[#746ccc]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Подробнее
                                    </a>
                                    <a href="{{ route('divisions.edit', [
                                        'id' => $division->id,
                                        'back_url' => url()->full(),
                                    ]) }}"
                                        class="mt-0.5 inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Редактировать
                                    </a>
                                    <form action="{{ route('divisions.delete', $division->id) }}" method="POST"
                                        class="inline-block mt-0.5"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить отделение \'{{ $division->name }}\'?');">
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
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-lg font-medium">Нет отделений</p>
                                        <p class="text-[#7F7F7F] mt-1">Создайте первое отделение для начала работы</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('includes.pagination', ['paginator' => $divisions])
    </div>
@endsection
