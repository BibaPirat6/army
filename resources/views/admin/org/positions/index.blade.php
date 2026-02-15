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
            {{-- сортировка по комиссариату --}}
            <div class="relative group inline-block">

                {{-- Кнопка --}}
                <button
                    class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200
               rounded-lg transition font-medium">

                    Фильтр по комиссариатам

                    <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div
                    class="absolute left-0 mt-2 w-72 bg-white border border-gray-200
                rounded-xl shadow-xl z-50 p-4
                opacity-0 invisible
                group-hover:opacity-100 group-hover:visible
                transition-all duration-200">

                    <form method="GET" action="{{ route('positions.index') }}">

                        {{-- Комиссариаты --}}
                        <div>
                            <span class="block text-sm font-semibold mb-2">
                                Комиссариат
                            </span>

                            <div class="max-h-48 overflow-y-auto space-y-2">

                                @foreach ($commissariats as $commissariat)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">

                                        <input type="radio" name="sort_commissariat[]" value="{{ $commissariat->id }}"
                                            {{ in_array($commissariat->id, (array) request('sort_commissariat', [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-[#A60644] focus:ring-[#A60644]">

                                        {{ $commissariat->name }}

                                    </label>
                                @endforeach

                            </div>
                        </div>

                        {{-- Кнопки --}}
                        <div class="mt-4 space-y-2">

                            <button type="submit"
                                class="w-full py-2 bg-[#A60644] text-white
                               rounded-lg hover:bg-[#8c0538] transition">
                                Применить
                            </button>

                            @if (request()->has('sort_commissariat'))
                                <a href="{{ route('positions.index') }}"
                                    class="block w-full text-center py-2 border border-gray-400
                              rounded-lg hover:bg-gray-100 transition">
                                    Сбросить
                                </a>
                            @endif

                        </div>

                    </form>

                </div>

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
                                <td class="px-6 py-4 text-[#060606]">
                                    @if (isset($position->positionType->name))
                                        {{ $position->positionType->name }}
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Нет
                                        </span>
                                    @endif
                                </td>
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
