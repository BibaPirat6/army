@extends('layouts.main')

@section('header-title')
    {{ $commissariat->name }}
@endsection

@section('vite-resources')
    @vite(['resources/css/structure.css', 'resources/js/structure.js'])
@endsection

@section('content')
    {{-- Кнопка сброса вида --}}
    <button id="resetView"
        class="fixed bottom-5 right-5 z-[1000] px-4 py-2.5 rounded-xl bg-[#060606] text-white text-sm font-medium hover:bg-[#060606]/80 transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
            </path>
        </svg>
        Вернуться к центру
    </button>

    {{-- Кнопка назад --}}
    <a href="{{ route('structure.index') }}"
        class="fixed left-5 top-20 z-[100] inline-flex items-center gap-2 px-4 py-2 bg-white/90 backdrop-blur-sm rounded-xl text-[#A60644] font-medium hover:bg-white shadow-md hover:shadow-lg transition-all duration-200 group">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Назад
    </a>

    {{-- Dropdown меню создания --}}
    <div class="fixed top-20 right-5 z-50">
        <div class="relative">
            <button
                class="dropdown-btn flex items-center gap-2 px-5 py-2.5 bg-white/90 backdrop-blur-sm border border-[#BFBFBF] rounded-xl font-semibold text-[#060606] hover:text-[#A60644] hover:border-[#A60644] transition-all duration-200 shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создание
                <svg class="dropdown-arrow w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <ul
                class="dropdown-menu absolute top-full right-0 mt-2 bg-white rounded-xl shadow-xl border border-[#BFBFBF] list-none p-2 min-w-[220px] hidden opacity-0 scale-95 transition-all duration-200 z-50">
                <li class="mb-1 last:mb-0">
                    <a href="{{ route('departments.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="block px-4 py-2.5 text-[#060606] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors">Отдел</a>
                </li>
                <li class="mb-1 last:mb-0">
                    <a href="{{ route('divisions.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="block px-4 py-2.5 text-[#060606] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors">Отделение</a>
                </li>
                <li class="mb-0">
                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="block px-4 py-2.5 text-[#060606] rounded-lg hover:bg-[#A60644]/10 hover:text-[#A60644] transition-colors">Сотрудник</a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Основной контейнер для панорамирования --}}
    <div id="viewport"
        class="w-screen h-screen bg-gradient-to-br from-[#f7f3f3] to-[#e8e4e4] cursor-grab active:cursor-grabbing overflow-hidden">
        <div id="canvas" class="absolute left-0 top-0 inline-block min-w-max p-8">
            <div class="tree flex flex-col items-center pt-[50px]">
                <div class="boss-wrapper flex flex-col items-center relative pt-[50px]">

                    {{-- Начальник комиссариата --}}
                    <a href="{{ route('commissariats.show', ['id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="chief-link block w-[360px] bg-gradient-to-r from-[#060606] to-[#1a1a1a] rounded-2xl shadow-2xl border border-white/10 card-hover no-underline">
                        <div class="flex items-center gap-4 p-5">
                            <div
                                class="w-16 h-16 rounded-full bg-gradient-to-br from-[#A60644] to-[#6b0229] flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-xs text-white/60 uppercase tracking-wider mb-1">Начальник комиссариата
                                </div>
                                @if(optional($commissariat->getChiefAttribute()))
                                    <div class="text-white font-semibold text-lg leading-tight">
                                        {{ optional($commissariat->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                    </div>
                                @else
                                    <div class="text-white/50 italic">Не назначен</div>
                                @endif
                            </div>
                            <div
                                class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-[#A60644] transition-all duration-200">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                            </div>
                        </div>
                    </a>

                    {{-- Линии --}}
                    <div class="lines-to-departments">
                        <div class="line vertical"></div>
                        <div class="line horizontal"></div>
                    </div>

                    {{-- ОТДЕЛЫ И ОСТАЛЬНЫЕ КАРТОЧКИ В ОДНУ ЛИНИЮ --}}
                    <div class="departments-container">






                        {{-- Карточки отделов --}}
                        @foreach ($commissariat->departments as $department)
                            <div
                                class="department-card w-[340px] bg-white rounded-2xl shadow-xl border border-[#BFBFBF]/30 overflow-hidden card-hover flex flex-col">
                                {{-- Заголовок отдела --}}
                                <div class="bg-gradient-to-r from-[#A60644] to-[#6b0229] px-5 py-3 flex-shrink-0">
                                    <h3 class="text-white font-bold text-xl uppercase tracking-wide">{{ $department->name }}
                                    </h3>
                                    <p class="text-white/60 text-xs mt-1">Отдел</p>
                                </div>

                                {{-- Начальник отдела --}}
                                <div class="p-4 border-b border-[#BFBFBF]/20 flex-shrink-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="text-xs text-[#565A5B] uppercase tracking-wider">Начальник отдела</div>
                                            @if(optional($department->getChiefAttribute()))
                                                <div class="text-[#060606] font-semibold">
                                                    {{ optional($department->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                                </div>
                                            @else
                                                <div class="text-[#565A5B] italic text-sm">Не назначен</div>
                                            @endif
                                        </div>
                                        <a href="{{ route('departments.show', ['id' => $department->id, 'back_url' => url()->full()]) }}"
                                            class="info-btn w-8 h-8 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#A60644]" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M12 16v.01" stroke-linecap="round"></path>
                                                <path d="M12 13a2 2 0 0 0 .914-3.782 1.98 1.98 0 0 0-2.414.483"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                {{-- Отделения отдела и сотрудники отдела --}}
                                <div class="p-4 space-y-3 flex-1 overflow-y-auto custom-scroll smooth-content"
                                    style="max-height: 400px;">
                                    {{-- Кнопка добавления отделения --}}
                                    <a href="{{ route('divisions.create', ['commissariat_id' => $commissariat->id, 'department_id' => $department->id, 'back_url' => url()->full()]) }}"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#A60644] text-white font-medium rounded-xl hover:bg-[#A60644]/80 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить отделение
                                    </a>


                                    {{-- Кнопка добавления сотрудника в отдел --}}
                                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'department_id' => $department->id, 'back_url' => url()->full()]) }}"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#A60644]/10 text-[#A60644] text-sm font-medium rounded-lg hover:bg-[#A60644] hover:text-white transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить сотрудника
                                    </a>


                                    {{-- Сотрудники отдела (не в отделениях, исключая начальника отдела) --}}
                                    @php
                                        // Получаем ID начальника отдела
                                        $departmentChiefId = optional($department->getChiefAttribute())->id;

                                        // Берём позиции, привязанные напрямую к отделу (без division), не самостоятельные,
                                        // исключая позицию начальника отдела, затем мапим на employee и убираем дубликаты.
                                        $departmentEmployees = $department->employeePositions()
                                            ->whereNull('division_id')
                                            ->where('employee_id', '!=', $departmentChiefId)
                                            ->with('employee.person')
                                            ->get()
                                            ->map(function ($pos) {
                                                return $pos->employee;
                                            })
                                            ->filter() // убрать null
                                            ->unique('id')
                                            ->values();
                                    @endphp

                                    @if($departmentEmployees->count() > 0)
                                        <div class="bg-[#f5f5f5] rounded-xl p-3 border border-[#A60644]/20">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-1 h-4 bg-[#A60644] rounded-full"></div>
                                                <h4 class="font-bold text-[#060606] text-sm uppercase tracking-wide">Сотрудники
                                                    отдела</h4>
                                            </div>
                                            <div class="space-y-1.5">
                                                @foreach ($departmentEmployees as $employee)
                                                    <div
                                                        class="flex items-center justify-between bg-white rounded-lg p-2 border border-[#BFBFBF]/20 hover:shadow-md transition-all duration-200">
                                                        <div class="flex-1">
                                                            <span class="text-sm text-[#060606] truncate block">
                                                                {{ $employee->getFullNameAttribute() ?? "Нет данных" }}
                                                            </span>
                                                        </div>
                                                        <a href="{{ route('employees.show', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                                            class="text-[#A60644] hover:text-[#6b0229] transition-colors text-xs font-medium ml-2">
                                                            Подробнее →
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Отделения отдела --}}
                                    @if($department->divisions->count() > 0)
                                        @foreach ($department->divisions as $division)
                                            @php
                                                // Получаем ID начальника отделения (если есть)
                                                $divisionChiefId = optional($division->getChiefAttribute())->id;

                                                // Получаем всех сотрудников отделения, исключая начальника
                                                $divisionEmployees = $division->employeePositions
                                                    ->filter(function ($position) use ($divisionChiefId) {
                                                        return $position->employee && $position->employee->id != $divisionChiefId;
                                                    })
                                                    ->values();
                                            @endphp

                                            <div
                                                class="bg-[#f5f5f5] rounded-xl p-3 border border-[#BFBFBF]/20 hover:border-[#A60644]/30 transition-all duration-200">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="font-bold text-[#060606] text-sm uppercase tracking-wide">
                                                        {{ $division->name }}
                                                    </h4>
                                                    <a href="{{ route('divisions.show', ['id' => $division->id, 'back_url' => url()->full()]) }}"
                                                        class="info-btn w-6 h-6 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-[#A60644]" fill="none" stroke="currentColor"
                                                            stroke-width="2" viewBox="0 0 24 24">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <path d="M12 16v.01" stroke-linecap="round"></path>
                                                            <path d="M12 13a2 2 0 0 0 .914-3.782 1.98 1.98 0 0 0-2.414.483"></path>
                                                        </svg>
                                                    </a>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="text-xs text-[#565A5B]">Начальник отделения</div>
                                                    @if(optional($division->getChiefAttribute()))
                                                        <div class="text-[#060606] font-medium text-sm">
                                                            {{ optional($division->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                                        </div>
                                                    @else
                                                        <div class="text-[#565A5B] italic text-xs">Не назначен</div>
                                                    @endif
                                                </div>

                                                {{-- Сотрудники отделения (исключая начальника) --}}
                                                @if($divisionEmployees->count() > 0)
                                                    <div class="mt-2 space-y-1.5">
                                                        @foreach ($divisionEmployees as $employeePosition)
                                                            <div
                                                                class="flex items-center justify-between bg-white rounded-lg p-2 border border-[#BFBFBF]/20 hover:shadow-md transition-all duration-200">
                                                                <div class="flex-1">
                                                                    <span class="text-sm text-[#060606] truncate block">
                                                                        {{ optional($employeePosition->employee)->getFullNameAttribute() ?? "Нет данных" }}
                                                                    </span>
                                                                </div>
                                                                <a href="{{ route('employees.show', ['id' => $employeePosition->employee->id, 'back_url' => url()->full()]) }}"
                                                                    class="text-[#A60644] hover:text-[#6b0229] transition-colors text-xs font-medium ml-2">
                                                                    Подробнее →
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-center text-[#565A5B] py-2 text-xs italic">Нет сотрудников</div>
                                                @endif

                                                {{-- Кнопка добавления сотрудника в отделение --}}
                                                <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'department_id' => $department->id, 'division_id' => $division->id, 'back_url' => url()->full()]) }}"
                                                    class="add-employee-btn mt-3 w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-all duration-200 shadow-md hover:shadow-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Добавить сотрудника
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-[#565A5B] py-6 italic">Нет отделений</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach






                        {{-- ОБЪЕДИНЕННАЯ КАРТОЧКА СОТРУДНИКОВ (комиссариат + самостоятельные) --}}
                        @php
                            // Получаем ID начальника комиссариата
                            $commissariatChiefId = optional($commissariat->getChiefAttribute())->id;

                            // Фильтрующая функция для исключения начальника
                            $filterChief = fn($employee) => $employee && $employee->id != $commissariatChiefId;

                            // Получаем штатных сотрудников, исключая начальника комиссариата
                            $employeesNotIndependent = ($commissariat?->employeesNotIndependent() ?? collect())
                                ->filter($filterChief)
                                ->values();

                            // Получаем самостоятельных сотрудников, исключая начальника комиссариата
                            $employeesIndependent = ($commissariat?->employeesIndependent() ?? collect())
                                ->filter($filterChief)
                                ->values();

                            $hasEmployees = $employeesNotIndependent->count() > 0 || $employeesIndependent->count() > 0;
                        @endphp

                        @if($hasEmployees)
                            <div
                                class="department-card w-[340px] bg-white rounded-2xl shadow-xl border border-[#BFBFBF]/30 overflow-hidden card-hover">
                                <div class="bg-gradient-to-r from-[#060606] to-[#1a1a1a] px-5 py-3">
                                    <h3 class="text-white font-bold text-lg">Сотрудники</h3>
                                    <p class="text-white/60 text-xs mt-1">Комиссариат и самостоятельные</p>
                                </div>
                                <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
                                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#A60644]/10 text-[#A60644] text-sm font-medium rounded-lg hover:bg-[#A60644] hover:text-white transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить сотрудника
                                    </a>
                                    {{-- Сотрудники комиссариата --}}
                                    @if($employeesNotIndependent->count() > 0)
                                        <div>
                                            <div
                                                class="text-xs font-semibold text-[#565A5B] uppercase tracking-wider mb-2 flex items-center gap-2">
                                                <div class="w-1 h-4 bg-[#A60644] rounded-full"></div>
                                                Штатные сотрудники
                                            </div>
                                            <div class="space-y-2">
                                                @foreach ($employeesNotIndependent as $employee)
                                                    <div
                                                        class="flex items-center justify-between bg-[#f5f5f5] rounded-lg p-3 border border-[#BFBFBF]/20 hover:shadow-md transition-all duration-200">
                                                        <div class="flex-1">
                                                            @if($employee->person)
                                                                <div class="text-[#060606] font-medium">{{ $employee->person->last_name }}
                                                                    {{ $employee->person->first_name }}
                                                                </div>
                                                                <div class="text-xs text-[#565A5B]">
                                                                    {{ $employee->person->patronymic ?? '' }}
                                                                </div>
                                                            @else
                                                                <div class="text-[#565A5B] italic">
                                                                    {{ $employee->getFullNameAttribute() ?? "Нет данных" }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('employees.show', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                                            class="text-[#A60644] hover:text-[#6b0229] transition-colors text-sm font-medium ml-2">
                                                            Подробнее →
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Самостоятельные сотрудники --}}
                                    @if($employeesIndependent->count() > 0)
                                        <div>
                                            <div
                                                class="text-xs font-semibold text-[#565A5B] uppercase tracking-wider mb-2 flex items-center gap-2 mt-3">
                                                <div class="w-1 h-4 bg-[#A60644] rounded-full"></div>
                                                Самостоятельные сотрудники
                                            </div>
                                            <div class="space-y-2">
                                                @foreach ($employeesIndependent as $employee)
                                                    <div
                                                        class="flex items-center justify-between bg-gradient-to-r from-[#f5f5f5] to-[#faf5f5] rounded-lg p-3 border border-[#A60644]/20 hover:shadow-md transition-all duration-200">
                                                        <div class="flex-1">
                                                            <div class="text-[#060606] font-medium">
                                                                {{ $employee->getFullNameAttribute() ?? "Нет данных" }}
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('employees.show', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                                            class="text-[#A60644] hover:text-[#6b0229] transition-colors text-sm font-medium ml-2">
                                                            Подробнее →
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif


                                </div>
                            </div>
                        @endif

                        {{-- САМОСТОЯТЕЛЬНЫЕ ОТДЕЛЕНИЯ (вне отделов) --}}
                        @php
                            $divisionsIndependent = $commissariat?->divisionsIntependent() ?? collect();
                        @endphp

                        @if($divisionsIndependent->count() > 0)
                            @foreach ($divisionsIndependent as $division)
                                <div
                                    class="department-card w-[340px] bg-white rounded-2xl shadow-xl border border-[#BFBFBF]/30 overflow-hidden card-hover flex flex-col">
                                    <div class="bg-gradient-to-r from-[#7F7F7F] to-[#5a5a5a] px-5 py-3 flex-shrink-0">
                                        <h3 class="text-white font-bold text-lg uppercase tracking-wide">{{ $division->name }}</h3>
                                        <p class="text-white/70 text-xs mt-1">Самостоятельное отделение</p>
                                    </div>

                                    {{-- Начальник отделения --}}
                                    <div class="p-4 border-b border-[#BFBFBF]/20 flex-shrink-0">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="text-xs text-[#565A5B] uppercase tracking-wider">Начальник</div>
                                                @if(optional($division->getChiefAttribute()))
                                                    <div class="text-[#060606] font-semibold">
                                                        {{ optional($division->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                                    </div>
                                                @else
                                                    <div class="text-[#565A5B] italic text-sm">Не назначен</div>
                                                @endif
                                            </div>
                                            <a href="{{ route('divisions.show', ['id' => $division->id, 'back_url' => url()->full()]) }}"
                                                class="info-btn w-8 h-8 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-[#A60644]" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M12 16v.01" stroke-linecap="round"></path>
                                                    <path d="M12 13a2 2 0 0 0 .914-3.782 1.98 1.98 0 0 0-2.414.483"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Сотрудники отделения (исключая начальника) --}}
                                    <div class="p-4 space-y-3 flex-1 overflow-y-auto custom-scroll smooth-content"
                                        style="max-height: 400px;">
                                        @php
                                            // Получаем ID начальника отделения (если есть)
                                            $chiefEmployeeId = optional($division->getChiefAttribute())->id;

                                            // Получаем всех сотрудников отделения, исключая начальника
                                            $regularEmployees = $division->employeePositions
                                                ->filter(function ($position) use ($chiefEmployeeId) {
                                                    return $position->employee && $position->employee->id != $chiefEmployeeId;
                                                })
                                                ->values();
                                        @endphp

                                        @if($regularEmployees->count() > 0)
                                            @foreach ($regularEmployees as $employeePosition)
                                                <div
                                                    class="flex items-center justify-between bg-[#f5f5f5] rounded-lg p-3 border border-[#BFBFBF]/20 hover:shadow-md transition-all duration-200">
                                                    <div class="flex-1">
                                                        @if(optional($employeePosition->employee)->getFullNameAttribute())
                                                            <div class="text-[#060606] font-medium">
                                                                {{ optional($employeePosition->employee)->getFullNameAttribute() ?? "" }}
                                                            </div>
                                                        @else
                                                            <div class="text-[#565A5B] italic">Нет данных</div>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('employees.show', ['id' => $employeePosition->employee->id, 'back_url' => url()->full()]) }}"
                                                        class="text-[#A60644] hover:text-[#6b0229] transition-colors text-sm font-medium ml-2">
                                                        Подробнее →
                                                    </a>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center text-[#565A5B] py-4 italic">Нет сотрудников</div>
                                        @endif


                                        {{-- Кнопка добавления сотрудника --}}
                                        <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'division_id' => $division->id, 'back_url' => url()->full()]) }}"
                                            class="add-employee-btn w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#A60644] text-white font-medium rounded-xl hover:bg-[#A60644]/80 transition-all duration-200 shadow-md hover:shadow-lg mt-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Добавить сотрудника
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
@endsection