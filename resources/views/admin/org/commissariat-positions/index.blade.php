@extends('layouts.main')

@section('header-title')
    Штатные должности комиссариата: {{ $commissariat->name }}
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <div class="w-full px-4 py-4 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <a href="{{ $backUrl ?? route('commissariats.index') }}"
                        class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200 text-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        К списку комиссариатов
                    </a>
                </div>
                <h1 class="text-xl font-bold text-[#060606]">Штатные должности</h1>
                <p class="text-sm text-[#565A5B]">{{ $commissariat->name }}</p>
            </div>
            <a href="{{ route('commissariat-positions.create', [
                'back_url' => url()->full(),
                'commissariat_id' => $commissariat->id,
                'employeeId' => $employeeId ?? null,
            ]) }}"
                class="inline-flex items-center px-4 py-2.5 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-all duration-200 shadow-md hover:shadow-lg active:scale-[0.98]">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать должность
            </a>
        </div>

        <!-- Фильтры -->
        <form method="GET" class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 mb-4" id="filterForm">
            <input type="hidden" name="commissariat_id" value="{{ $commissariat->id }}">

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 xl:grid-cols-8 gap-3 mb-3">
                <div class="col-span-2 md:col-span-1 relative">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="search" name="search" value="{{ $filters->search }}"
                        placeholder="Поиск..."
                        class="w-full pl-9 pr-3 py-2 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black focus:border-black outline-none transition">
                </div>

                <div>
                    <select id="vacancy_status" name="vacancy_status"
                        class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black outline-none transition bg-white">
                        <option value="">Статус вакансии</option>
                        <option value="vacant" @selected($filters->vacancyStatus === 'vacant')>🔴 Вакант</option>
                        <option value="staffed" @selected($filters->vacancyStatus === 'staffed')>🟢 Укомплектовано</option>
                    </select>
                </div>

                <div>
                    <select id="employee_status" name="employee_status"
                        class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black outline-none transition bg-white">
                        <option value="">Статус сотрудника</option>
                        <option value="working" @selected($filters->employeeStatus === 'working')>💼 Работает</option>
                        <option value="vacation" @selected($filters->employeeStatus === 'vacation')>🏖️ Отпуск</option>
                        <option value="maternity" @selected($filters->employeeStatus === 'maternity')>👶 Декрет</option>
                    </select>
                </div>

                <div class="relative z-30">
                    <select id="department_id" name="department_id"
                        class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black outline-none transition bg-white focus:z-50">
                        <option value="">Отдел</option>
                        @foreach ($departments as $item)
                            <option value="{{ $item->id }}" @selected($filters->departmentId == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative z-30">
                    <select id="division_id" name="division_id"
                        class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black outline-none transition bg-white focus:z-50">
                        <option value="">Отделение</option>
                        @foreach ($divisions as $item)
                            <option value="{{ $item->id }}" @selected($filters->divisionId == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select id="sort_by" name="sort_by"
                        class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black outline-none transition bg-white">
                        <option value="id" @selected($filters->sortBy === 'id')>Сортировать по</option>
                        <option value="rate_total" @selected($filters->sortBy === 'rate_total')>Ставке</option>
                        <option value="vacancy_status" @selected($filters->sortBy === 'vacancy_status')>Статусу вакансии</option>
                        <option value="occupied_rate" @selected($filters->sortBy === 'occupied_rate')>Занятым ставкам</option>
                        <option value="available_rate" @selected($filters->sortBy === 'available_rate')>Свободным ставкам</option>
                    </select>
                </div>

                <div>
                    <select id="sort_direction" name="sort_direction"
                        class="w-full py-2 px-3 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black outline-none transition bg-white">
                        <option value="desc" @selected($filters->sortDirection === 'desc')>↓ По убыванию</option>
                        <option value="asc" @selected($filters->sortDirection === 'asc')>↑ По возрастанию</option>
                    </select>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-[#565A5B] whitespace-nowrap">Ставка:</span>
                        <span id="rate_min_label"
                            class="font-semibold text-[#A60644]">{{ $filters->rateMin ?? 0.25 }}</span>
                        <span class="text-[#BFBFBF]">—</span>
                        <span id="rate_max_label" class="font-semibold text-[#A60644]">{{ $filters->rateMax ?? 2 }}</span>
                    </div>
                    <div class="relative mt-1 px-0.5">
                        <div class="relative h-1.5">
                            <div class="absolute inset-0 bg-gray-200 rounded-full"></div>
                            <div id="rate_range_track" class="absolute inset-y-0 bg-[#A60644] rounded-full"
                                style="left: 0%; right: 0%;"></div>
                            <input type="range" id="rate_min" name="rate_min" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMin ?? 0.25 }}"
                                class="absolute inset-y-0 w-full appearance-none bg-transparent pointer-events-none z-20
                        [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none 
                        [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 
                        [&::-webkit-slider-thumb]:bg-[#A60644] [&::-webkit-slider-thumb]:rounded-full 
                        [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-pointer
                        [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 
                        [&::-moz-range-thumb]:bg-[#A60644] [&::-moz-range-thumb]:border-0
                        [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:cursor-pointer">
                            <input type="range" id="rate_max" name="rate_max" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMax ?? 2 }}"
                                class="absolute inset-y-0 w-full appearance-none bg-transparent pointer-events-none z-30
                        [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none 
                        [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 
                        [&::-webkit-slider-thumb]:bg-[#A60644] [&::-webkit-slider-thumb]:rounded-full 
                        [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-pointer
                        [&::-moz-range-thumb]:w-4 [&::-moz-range-thumb]:h-4 
                        [&::-moz-range-thumb]:bg-[#A60644] [&::-moz-range-thumb]:border-0
                        [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-black transition shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Применить
                </button>
                <a href="{{ route('commissariat-positions.index', ['commissariat_id' => $commissariat->id]) }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Сбросить
                </a>
            </div>
        </form>

        <!-- Компактная таблица -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-800 sticky top-0 z-10">
                        <tr>
                            <th
                                class="px-3 py-2.5 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider w-[25%]">
                                Должность</th>
                            <th
                                class="px-3 py-2.5 text-center text-xs font-semibold text-gray-200 uppercase tracking-wider w-[15%]">
                                Ставки</th>
                            <th
                                class="px-3 py-2.5 text-left text-xs font-semibold text-gray-200 uppercase tracking-wider w-[35%]">
                                Назначения</th>
                            <th
                                class="px-3 py-2.5 text-center text-xs font-semibold text-gray-200 uppercase tracking-wider w-[12%]">
                                Статус</th>
                            <th
                                class="px-3 py-2.5 text-right text-xs font-semibold text-gray-200 uppercase tracking-wider w-[13%]">
                                Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($commissariatPositions as $pos)
                            <tr class="hover:bg-indigo-50/50 transition-colors duration-150 group">
                                <!-- Должность -->
                                <td class="px-3 py-2.5">
                                    <div class="font-medium text-gray-900 text-xs truncate max-w-[250px]"
                                        title="{{ $pos->position->name }}">
                                        {{ $pos->position->name }}
                                    </div>
                                    @if ($pos->department || $pos->division)
                                        <div class="text-[10px] text-gray-500 mt-0.5">
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
                                <td class="px-3 py-2.5">
                                    <div class="space-y-0.5">
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-gray-500">Всего:</span>
                                            <span
                                                class="font-bold text-gray-900">{{ number_format($pos->rate_total, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-gray-500">Занято:</span>
                                            <span
                                                class="font-bold text-green-600">{{ number_format($pos->occupied_rate, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-[10px]">
                                            <span class="text-gray-500">Свободно:</span>
                                            <span
                                                class="font-bold {{ $pos->has_vacancy ? 'text-blue-600' : 'text-red-600' }}">
                                                {{ number_format($pos->available_rate, 2) }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                            @php
                                                $percent =
                                                    $pos->rate_total > 0
                                                        ? ($pos->occupied_rate / $pos->rate_total) * 100
                                                        : 0;
                                            @endphp
                                            <div class="bg-[#A60644] h-1 rounded-full transition-all duration-300"
                                                style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Назначения -->
                                <td class="px-3 py-2.5">
                                    @if ($pos->employeePositions->count() > 0)
                                        <div class="space-y-1">
                                            @foreach ($pos->employeePositions as $assignment)
                                                @php
                                                    $statusId = $assignment->employee_position_status_id;
                                                    $isWorking = $statusId == 1;
                                                    $rate = $isWorking ? number_format($assignment->rate, 2) : '';
                                                @endphp
                                                <div class="flex items-center justify-between gap-1.5 text-[11px]">
                                                    <div class="flex items-center gap-1 min-w-0">
                                                        <a href="{{ route('employees.show', [
                                                            'id' => $assignment->employee->id,
                                                            'back_url' => url()->full(),
                                                        ]) }}"
                                                            class="text-indigo-600 hover:text-indigo-800 hover:underline font-medium truncate max-w-[150px]"
                                                            title="{{ $assignment->employee->getFullNameAttribute() }}">
                                                            {{ $assignment->employee->getFullNameAttribute() }}
                                                        </a>
                                                        @if ($statusId == 1)
                                                            <span
                                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-green-100 text-green-800 whitespace-nowrap">💼
                                                                работает</span>
                                                        @elseif($statusId == 2)
                                                            <span
                                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-yellow-100 text-yellow-800 whitespace-nowrap">🏖️
                                                                отпуск</span>
                                                        @elseif($statusId == 3)
                                                            <span
                                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-purple-100 text-purple-800 whitespace-nowrap">👶
                                                                декрет</span>
                                                        @endif
                                                    </div>
                                                    @if ($isWorking)
                                                        <span
                                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                                                            {{ $rate }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-[11px] italic">Нет назначений</span>
                                    @endif
                                </td>

                                <!-- Статус вакансии -->
                                <td class="px-3 py-2.5 text-center">
                                    @php
                                        $availableRate =
                                            $pos->available_rate ?? $pos->rate - ($pos->occupied_rate ?? 0);
                                        $isVacant = $availableRate > 0;
                                    @endphp
                                    @if ($isVacant)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-red-50 text-red-700 text-[10px] font-semibold border border-red-200">
                                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            ВАКАНТНО
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-green-50 text-green-700 text-[10px] font-semibold border border-green-200">
                                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            УКОМПЛЕКТОВАНО
                                        </span>
                                    @endif
                                </td>

                                <!-- Действия -->
                                <td class="px-3 py-2.5 text-right">
                                    <div
                                        class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        @if ($pos->has_vacancy)
                                            <a href="{{ route('commissariat-positions.assign.create', [
                                                'id' => $pos->id,
                                                'back_url' => url()->full(),
                                                'commissariat_id' => $commissariat->id,
                                                'employeeId' => $employeeId ?? null,
                                            ]) }}"
                                                class="inline-flex items-center p-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-150 shadow-sm hover:shadow-md"
                                                title="Назначить сотрудника">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </a>
                                        @endif

                                        {{-- Кнопка редактирования --}}
                                        <a href="{{ route('commissariat-positions.edit', [
                                            'id' => $pos->id,
                                            'back_url' => url()->full(),
                                        ]) }}"
                                            class="inline-flex items-center p-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors duration-150 shadow-sm hover:shadow-md"
                                            title="Редактировать">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>

                                        <a href="{{ route(
                                            'commissariat-positions.show',
                                            array_filter([
                                                'id' => $pos->id,
                                                'back_url' => url()->full(),
                                                'commissariat_id' => $commissariat->id,
                                                'employeeId' => $employeeId ?? null,
                                            ]),
                                        ) }}"
                                            class="inline-flex items-center p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-150 shadow-sm hover:shadow-md"
                                            title="Подробнее">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>

                                        <form
                                            action="{{ route('commissariat-positions.delete', ['id' => $pos->id, 'back_url' => url()->full()]) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Удалить штатную должность?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center p-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-150 shadow-sm hover:shadow-md"
                                                title="Удалить">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                        <p class="text-gray-500 font-medium">Нет штатных должностей</p>
                                        <p class="text-xs text-gray-400 mt-1">Создайте первую штатную должность</p>
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

@push('scripts')
    <script>
        const rateMin = document.getElementById('rate_min');
        const rateMax = document.getElementById('rate_max');
        const rateMinLabel = document.getElementById('rate_min_label');
        const rateMaxLabel = document.getElementById('rate_max_label');
        const rateRangeTrack = document.getElementById('rate_range_track');

        function updateRateRange() {
            const min = parseFloat(rateMin.value);
            const max = parseFloat(rateMax.value);

            if (min > max) {
                if (this === rateMin) {
                    rateMax.value = min;
                } else {
                    rateMin.value = max;
                }
            }

            const finalMin = parseFloat(rateMin.value);
            const finalMax = parseFloat(rateMax.value);

            const minPercent = ((finalMin - 0.25) / (2 - 0.25)) * 100;
            const maxPercent = ((finalMax - 0.25) / (2 - 0.25)) * 100;

            rateRangeTrack.style.left = minPercent + '%';
            rateRangeTrack.style.right = (100 - maxPercent) + '%';

            rateMinLabel.textContent = finalMin;
            rateMaxLabel.textContent = finalMax;
        }

        rateMin.addEventListener('input', updateRateRange);
        rateMax.addEventListener('input', updateRateRange);

        updateRateRange();
    </script>
@endpush
