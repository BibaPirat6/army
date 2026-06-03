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



        <form method="GET" class="bg-white shadow-md rounded-xl p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-4">
                <input type="hidden" name="commissariat_id" value="{{ $commissariat->id }}">

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="search" name="search" value="{{ $filters->search }}"
                            placeholder="Поиск по должностям..."
                            class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                    </div>
                </div>

                <div>
                    <label for="vacancy_status" class="block text-sm font-medium text-gray-700 mb-1">Статус штатной
                        должности</label>
                    <select id="vacancy_status" name="vacancy_status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                        <option value="">
                            Все статусы
                        </option>
                        <option value="vacant" @selected($filters->vacancyStatus === 'vacant')>
                            🔴 Вакант
                        </option>
                        <option value="staffed" @selected($filters->vacancyStatus === 'staffed')>
                            🟢 Укомплектовано
                        </option>
                    </select>
                </div>

                <div>
                    <label for="employee_status" class="block text-sm font-medium text-gray-700 mb-1">Статус назначения
                        сотрудника</label>
                    <select id="employee_status" name="employee_status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                        <option value="">
                            Все
                        </option>
                        <option value="working">
                            💼 Работает
                        </option>
                        <option value="vacation">
                            🏖️ Отпуск
                        </option>
                        <option value="maternity">
                            👶 Декрет
                        </option>
                    </select>
                </div>

                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Отдел</label>
                    <select id="department_id" name="department_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                        <option value="">
                            Все отделы
                        </option>
                        @foreach ($departments as $item)
                            <option value="{{ $item->id }}" @selected($filters->departmentId == $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="division_id" class="block text-sm font-medium text-gray-700 mb-1">Отделение</label>
                    <select id="division_id" name="division_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                        <option value="">
                            Все отделения
                        </option>
                        @foreach ($divisions as $item)
                            <option value="{{ $item->id }}" @selected($filters->divisionId == $item->id)>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sort_direction" class="block text-sm font-medium text-gray-700 mb-1">Сортировка</label>
                    <select id="sort_direction" name="sort_direction"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                        <option value="desc">
                            ↓ По убыванию
                        </option>
                        <option value="asc">
                            ↑ По возрастанию
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ставка: <span id="rate_min_label"
                            class="text-indigo-600 font-semibold">{{ $filters->rateMin ?? 0.25 }}</span> — <span
                            id="rate_max_label" class="text-indigo-600 font-semibold">{{ $filters->rateMax ?? 2 }}</span>
                    </label>
                    <div class="relative pt-1 px-1">
                        <div class="relative h-2">
                            <div class="absolute inset-0 bg-gray-200 rounded-lg"></div>
                            <div id="rate_range_track" class="absolute inset-y-0 bg-indigo-400 rounded-lg"
                                style="left: 0%; right: 0%;"></div>
                            <input type="range" id="rate_min" name="rate_min" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMin ?? 0.25 }}"
                                class="absolute inset-y-0 w-full appearance-none bg-transparent pointer-events-none z-20
                               [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none 
                               [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 
                               [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:rounded-full 
                               [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-pointer
                               [&::-webkit-slider-thumb]:hover:bg-indigo-700 [&::-webkit-slider-thumb]:transition [&::-webkit-slider-thumb]:duration-150
                               [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none 
                               [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:bg-indigo-600 
                               [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:shadow-md
                               [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:hover:bg-indigo-700">
                            <input type="range" id="rate_max" name="rate_max" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMax ?? 2 }}"
                                class="absolute inset-y-0 w-full appearance-none bg-transparent pointer-events-none z-30
                               [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none 
                               [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 
                               [&::-webkit-slider-thumb]:bg-indigo-600 [&::-webkit-slider-thumb]:rounded-full 
                               [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-pointer
                               [&::-webkit-slider-thumb]:hover:bg-indigo-700 [&::-webkit-slider-thumb]:transition [&::-webkit-slider-thumb]:duration-150
                               [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none 
                               [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:bg-indigo-600 
                               [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:shadow-md
                               [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:hover:bg-indigo-700">
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0.25</span>
                        <span>0.5</span>
                        <span>0.75</span>
                        <span>1</span>
                        <span>1.25</span>
                        <span>1.5</span>
                        <span>1.75</span>
                        <span>2</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button type="submit"
                    class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-150 ease-in-out shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Применить
                </button>

                <a href="{{ route('commissariat-positions.index', ['commissariat_id' => $commissariat->id]) }}"
                    class="inline-flex items-center px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
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

                                <!-- Назначения (все сотрудники: работающие, отпуск, декрет) -->
                                <td class="px-6 py-4">
                                    @if ($pos->employeePositions->count() > 0)
                                        <div class="space-y-2">
                                            @foreach ($pos->employeePositions as $assignment)
                                                @php
                                                    $statusId = $assignment->employee_position_status_id;
                                                    $statusName = $assignment->employeePositionStatus->name ?? '';
                                                    $isWorking = $statusId == 1;
                                                    $rate = $isWorking ? number_format($assignment->rate, 2) : '';
                                                @endphp

                                                <div class="flex items-center justify-between gap-2 text-sm">
                                                    <div class="flex items-center gap-2">
                                                        <a href="{{ route('employees.show', [
                                                            'id' => $assignment->employee->id,
                                                            'back_url' => url()->full(),
                                                        ]) }}"
                                                            class="text-[#A60644] hover:underline font-medium">
                                                            {{ $assignment->employee->getFullNameAttribute() }}
                                                        </a>
                                                        <!-- Бейдж статуса -->
                                                        @if ($statusId == 1)
                                                            <span
                                                                class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-medium">
                                                                💼 Работает
                                                            </span>
                                                        @elseif($statusId == 2)
                                                            <span
                                                                class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded text-xs font-medium">
                                                                🏖️ Отпуск
                                                            </span>
                                                        @elseif($statusId == 3)
                                                            <span
                                                                class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-xs font-medium">
                                                                👶 Декрет
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Ставка (только для работающих) -->
                                                    @if ($isWorking)
                                                        <span
                                                            class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium">
                                                            ставка: {{ $rate }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500 text-sm">Нет назначений</span>
                                    @endif
                                </td>



                                <!-- Статус вакансии -->
                                <td class="px-6 py-4">
                                    @php
                                        $availableRate =
                                            $pos->available_rate ?? $pos->rate - ($pos->occupied_rate ?? 0);
                                        $isVacant = $availableRate > 0;
                                    @endphp

                                    @if ($isVacant)
                                        <div
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-red-100 text-red-800 text-sm font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            ВАКАНТНО
                                        </div>
                                        @if ($availableRate > 0 && $availableRate < $pos->rate)
                                            <div class="text-xs text-gray-500 mt-1">
                                                свободно: {{ number_format($availableRate, 2) }} из
                                                {{ number_format($pos->rate, 2) }}
                                            </div>
                                        @endif
                                    @else
                                        <div
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-green-100 text-green-800 text-sm font-medium">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            УКОМПЛЕКТОВАНО
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
