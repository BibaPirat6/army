@extends('layouts.main')

@section('header-title')
    Должности
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif


    <div class="w-full p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#060606]">Должности</h1>
                <p class="text-[#565A5B] mt-1">Список всех должностей</p>
            </div>


            <div class="relative inline-block">
                <a href="{{ route('positions.create') }}"
                    class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Создать должность
                </a>
            </div>
        </div>


        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[#e7e1e1] uppercase tracking-wider">ID
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[#e7e1e1] uppercase tracking-wider">
                                Название</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[#e7e1e1] uppercase tracking-wider">
                                Тип должности</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-[#e7e1e1] uppercase tracking-wider">
                                Тип начальства</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-[#e7e1e1] uppercase tracking-wider">
                                Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($positions as $position)
                            <tr class="group hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td
                                    class="px-6 py-4 text-[#060606] font-medium group-hover:bg-[#A60644]/5 transition-colors duration-200">
                                    {{ $position->id }}
                                </td>
                                <td class="px-6 py-4 text-[#060606] group-hover:bg-[#A60644]/5 transition-colors duration-200">
                                    {{ $position->name }}
                                </td>
                                <td class="px-6 py-4 text-[#060606] group-hover:bg-[#A60644]/5 transition-colors duration-200">
                                    <span class="text-sm text-[#565A5B]">{{ $position->getPositionTypeNameAttribute() }}</span>
                                </td>
                                <td class="px-6 py-4 text-[#060606] group-hover:bg-[#A60644]/5 transition-colors duration-200">
                                    <span class="text-sm text-[#565A5B]">
                                        {{ $position->getChiefTypeNameAttribute() }}</span>
                                </td>
                                <td class="px-6 py-4 group-hover:bg-[#A60644]/5 transition-colors duration-200">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Подробнее -->
                                        <a href="{{ route('positions.show', $position->id) }}"
                                            class="group/action p-2 rounded-lg bg-[#565A5B] text-[#fff] hover:bg-[#746c6f] hover:text-white transition-all duration-200"
                                            title="Подробнее">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <!-- Редактировать -->
                                        <a href="{{ route('positions.edit', $position->id) }}"
                                            class="group/action p-2 rounded-lg bg-[#A60644] text-[#fff] hover:bg-[#A60644] hover:text-white transition-all duration-200"
                                            title="Редактировать">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Удалить -->
                                        <form action="{{ route('positions.delete', $position->id) }}" method="POST"
                                            onsubmit="return confirm('Вы уверены, что хотите удалить должность «{{ $position->name }}»?');"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="group/action p-2 rounded-lg bg-red-600 text-[#fff] hover:bg-[#060606] hover:text-white transition-all duration-200"
                                                title="Удалить">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-[#060606]">Список пуст</h3>
                                        <p class="text-[#565A5B] mt-1 max-w-sm">Здесь пока нет должностей. Нажмите кнопку
                                            "Создать", чтобы добавить первую запись.</p>
                                        <a href="{{ route('positions.create') }}"
                                            class="mt-4 text-[#A60644] font-medium hover:underline text-sm">Создать
                                            должность &rarr;</a>
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