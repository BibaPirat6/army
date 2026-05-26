@extends('layouts.main')

@section('header-title')
    Штатные должности комиссариата: {{ $commissariat->name }}
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif


    <div class="w-full p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center mb-4">
                    <a href="{{ $backUrl ?? route('commissariats.index') }}"
                        class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Назад к списку комиссариатов
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-[#060606]">Штатные должности</h1>
                <p class="text-[#565A5B] mt-1">{{ $commissariat->name }}</p>
            </div>
            <a href="{{ route('commissariat-positions.create', [
                'back_url' => url()->full(),
                'commissariat_id' => $commissariat->id,
                'employeeId' => $employeeId ?? null,
            ]) }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать штатную должность
            </a>
        </div>



        

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Должность</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Ставки</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Назначения</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Статус</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($commissariatPositions as $pos)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <!-- Должность -->
                                <td class="px-6 py-4">
                                    <div class="font-medium text-[#060606]">
                                        {{ $pos->position->name }}
                                    </div>
                                    @if ($pos->department || $pos->division)
                                        <div class="text-xs text-[#565A5B] mt-1">
                                            @if ($pos->department)
                                                {{ $pos->department->name }}
                                            @endif
                                            @if ($pos->division)
                                                / {{ $pos->division->name }}
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <!-- Ставки -->
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-sm">
                                            <span class="font-medium">Всего:</span>
                                            <span
                                                class="text-[#A60644] font-bold">{{ number_format($pos->rate_total, 2) }}</span>
                                        </div>
                                        <div class="text-sm">
                                            <span class="font-medium">Занято:</span>
                                            <span
                                                class="text-green-600 font-bold">{{ number_format($pos->occupied_rate, 2) }}</span>
                                        </div>
                                        <div class="text-sm">
                                            <span class="font-medium">Свободно:</span>
                                            <span
                                                class="{{ $pos->has_vacancy ? 'text-blue-600' : 'text-red-600' }} font-bold">
                                                {{ number_format($pos->available_rate, 2) }}
                                            </span>
                                        </div>
                                        <!-- Прогресс-бар -->
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                            @php
                                                $percent =
                                                    $pos->rate_total > 0
                                                        ? ($pos->occupied_rate / $pos->rate_total) * 100
                                                        : 0;
                                            @endphp
                                            <div class="bg-[#A60644] h-1.5 rounded-full"
                                                style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Назначения (только работающие сотрудники) -->
                                <td class="px-6 py-4">
                                    @if ($pos->employeePositions->count() > 0)
                                        <div class="space-y-2">
                                            @foreach ($pos->employeePositions as $assignment)
                                                <div class="flex items-center justify-between gap-2 text-sm">
                                                    <a href="{{ route('employees.show', [
                                                        'id' => $assignment->employee->id,
                                                        'back_url' => url()->full(),
                                                    ]) }}"
                                                        class="text-[#A60644] hover:underline font-medium">
                                                        {{ $assignment->employee->getFullNameAttribute() }}
                                                    </a>
                                                    <span
                                                        class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-medium">
                                                        ставка: {{ number_format($assignment->rate, 2) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500 text-sm">Нет назначений</span>
                                    @endif
                                </td>

                                <!-- Статус вакансии -->
                                <td class="px-6 py-4">
                                    @if ($pos->has_vacancy)
                                        <div
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-blue-100 text-blue-800 text-sm font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            ВАКАНТ
                                        </div>
                                    @else
                                        <div
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-green-100 text-green-800 text-sm font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Укомплектовано
                                        </div>
                                    @endif
                                </td>

                                <!-- Действия -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <!-- Кнопка назначения (только если есть вакансия) -->
                                        @if ($pos->has_vacancy)
                                            <a href="{{ route('commissariat-positions.assign.create', [
                                                'id' => $pos->id,
                                                'back_url' => url()->full(),
                                                'commissariat_id' => $commissariat->id,
                                                'employeeId' => $employeeId ?? null,
                                            ]) }}"
                                                class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Назначить
                                            </a>
                                        @endif

                                        <!-- Кнопка подробнее -->
                                        <a href="{{ route(
                                            'commissariat-positions.show',
                                            array_filter([
                                                'id' => $pos->id,
                                                'back_url' => url()->full(),
                                                'commissariat_id' => $commissariat->id,
                                                'employeeId' => $employeeId ?? null,
                                            ]),
                                        ) }}"
                                            class="inline-flex items-center px-3 py-2 bg-[#446ca4] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Подробнее
                                        </a>

                                        <!-- Кнопка удаления -->
                                        <form
                                            action="{{ route('commissariat-positions.delete', [
                                                'id' => $pos->id,
                                                'back_url' => url()->full(),
                                            ]) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Вы уверены, что хотите удалить штатную должность?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-lg font-medium">Нет штатных должностей</p>
                                        <p class="text-sm text-[#565A5B] mt-2">Создайте первую штатную должность для этого
                                            комиссариата</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
