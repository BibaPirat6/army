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
        class="fixed bottom-3 right-5 z-[1000] px-3 py-2.5 border-none rounded-md bg-[#060606] text-white cursor-pointer text-sm hover:opacity-85 transition-opacity">
        Вернуться к центру
    </button>

    {{-- Кнопка назад --}}
    <a href="{{ route('structure.index') }}"
        class="absolute left-4 mt-2 inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200 z-[100]">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Назад
    </a>

    {{-- Dropdown меню создания --}}
    <div class="fixed top-20 right-5 z-50">
        <div class="relative">
            <button
                class="dropdown-btn flex items-center gap-1 px-4 py-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg font-bold hover:text-[#A60644]">
                <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создание
                <svg class="dropdown-arrow w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <ul
                class="dropdown-menu absolute top-full right-0 mt-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-lg list-none p-2 min-w-[220px] hidden opacity-0 scale-95 transition-all duration-200">
                <li class="mb-1 last:mb-0">
                    <a href="{{ route('departments.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="block px-4 py-2 text-[#060606] rounded hover:bg-[#A60644]/10 hover:text-[#A60644]">Отдел</a>
                </li>
                <li class="mb-1 last:mb-0">
                    <a href="{{ route('divisions.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="block px-4 py-2 text-[#060606] rounded hover:bg-[#A60644]/10 hover:text-[#A60644]">Отделение</a>
                </li>
                <li class="mb-0">
                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="block px-4 py-2 text-[#060606] rounded hover:bg-[#A60644]/10 hover:text-[#A60644]">Сотрудник</a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Основной контейнер для панорамирования --}}
    <div id="viewport" class="w-screen h-screen bg-[#f7f3f3] cursor-grab active:cursor-grabbing overflow-hidden">
        <div id="canvas" class="absolute left-0 top-0 transform-origin-0-0 inline-block min-w-max">
            <div class="tree flex flex-col items-center pt-[100px]">
                <div class="boss-wrapper flex flex-col items-center relative pt-[50px]">

                    {{-- Начальник комиссариата --}}
                    <div
                        class="node boss flex items-center justify-start gap-5 h-[100px] bg-[#060606] text-white p-5 rounded-lg text-left relative overflow-hidden">
                        <a href="{{ route('commissariats.show', ['id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                            class="node-info" aria-label="Подробнее">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </a>
                        <div class="data">
                            @if(optional($commissariat->getChiefAttribute()))
                                <p class="data__fio text-base text-white">
                                    {{ optional($commissariat->getChiefAttribute())->getFullNameAttribute() ?? "" }}</p>
                            @else
                                <p>Не назначен начальник</p>
                            @endif
                        </div>
                    </div>

                    {{-- Линии --}}
                    <div class="lines-to-departments">
                        <div class="line vertical"></div>
                        <div class="line horizontal"></div>
                    </div>

                    {{-- ОТДЕЛЫ --}}
                    <div class="departments flex items-start gap-5 mt-5">
                        @foreach ($commissariat->departments as $department)
                            <div class="department bg-[#BFBFBF] p-5 rounded-lg flex-shrink-0">
                                <div class="dept-title bg-transparent text-[#060606] font-bold text-[30px] uppercase mb-3">
                                    {{ $department->name }}</div>

                                {{-- Начальник отдела --}}
                                <div
                                    class="node boss flex items-center justify-start gap-5 h-[100px] bg-[#060606] text-white p-5 rounded-lg text-left relative overflow-hidden">
                                    <a href="{{ route('departments.show', ['id' => $department->id, 'back_url' => url()->full()]) }}"
                                        class="node-info" aria-label="Подробнее">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                        </svg>
                                    </a>
                                    <div class="data">
                                        @if(optional($department->getChiefAttribute()))
                                            <p class="data__fio text-base text-white">
                                                {{ optional($department->getChiefAttribute())->getFullNameAttribute() ?? "" }}</p>
                                        @else
                                            <p>Не назначен начальник отдела</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Отделения отдела --}}
                                @if($department->divisions->count() > 0)
                                    <div class="units grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                                        @foreach ($department->divisions as $division)
                                            <div class="unit bg-[#7F7F7F] p-2.5 rounded-lg">
                                                <div class="unit-title text-left text-white text-xl uppercase mb-2">
                                                    {{ $division->name }}</div>

                                                {{-- Начальник отделения --}}
                                                <div
                                                    class="node boss flex items-center justify-start gap-5 h-[100px] bg-[#060606] text-white p-5 rounded-lg text-left relative overflow-hidden">
                                                    <a href="{{ route('divisions.show', ['id' => $division->id, 'back_url' => url()->full()]) }}"
                                                        class="node-info" aria-label="Подробнее">
                                                         <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                        </svg>
                                                    </a>
                                                    <div class="data">
                                                        @if(optional($division->getChiefAttribute()))
                                                            <p class="data__fio text-base text-white">
                                                                {{ optional($division->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                                            </p>
                                                        @else
                                                            <p>Не назначен начальник</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Сотрудники отделения --}}
                                                @if($division->employeePositions->count() > 0)
                                                    <div class="employees grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5 mt-2.5">
                                                        @foreach ($division->employeePositions as $employeePosition)
                                                            <div
                                                                class="employee flex flex-col p-1.5 bg-[#565A5B] rounded-md h-full relative overflow-hidden">
                                                                <a href="{{ route('employees.show', ['id' => $employeePosition->employee->id, 'back_url' => url()->full()]) }}"
                                                                    class="node-info" aria-label="Подробнее">
                                                                     <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <circle cx="12" cy="12" r="10"></circle>
                                                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                                    </svg>
                                                                </a>
                                                                <div class="employee__data">
                                                                    <div class="employee__fio text-left text-white text-xs">
                                                                        @if(optional($employeePosition->employee)->getFullNameAttribute())
                                                                            <p>{{ optional($employeePosition->employee)->getFullNameAttribute() ?? "" }}
                                                                            </p>
                                                                        @else
                                                                            <p>Нет данных</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                        <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'department_id' => $department->id, 'division_id' => $division->id, 'back_url' => url()->full()]) }}"
                                                            class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                            Добавить сотрудника
                                                        </a>
                                                    </div>
                                                @else
                                                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'department_id' => $department->id, 'division_id' => $division->id, 'back_url' => url()->full()]) }}"
                                                        class="mt-2 inline-flex items-center px-6 py-3 bg-[#a37084] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Добавить сотрудника
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                        <a href="{{ route('divisions.create', ['commissariat_id' => $commissariat->id, 'department_id' => $department->id, 'back_url' => url()->full()]) }}"
                                            class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Добавить отделение
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('divisions.create', ['commissariat_id' => $commissariat->id, 'department_id' => $department->id, 'back_url' => url()->full()]) }}"
                                        class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить отделение
                                    </a>
                                @endif
                            </div>
                        @endforeach

                        {{-- Сотрудники комиссариата --}}
                        @if($commissariat?->employeesNotIndependent()?->count() > 0)
                            <div class="department bg-[#BFBFBF] p-5 rounded-lg flex-shrink-0">
                                <h3 class="text-lg font-bold text-[#060606] mb-3">Сотрудники комиссариата</h3>
                                <div class="employees grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @foreach ($commissariat?->employeesNotIndependent() as $employee)
                                        <div
                                            class="employee flex flex-col p-1.5 bg-[#565A5B] rounded-md h-full relative overflow-hidden">
                                            <a href="{{ route('employees.show', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                                class="node-info" aria-label="Подробнее">
                                                 <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                </svg>
                                            </a>
                                            <div class="employee__data">
                                                <div class="employee__fio text-left text-white text-xs">
                                                    @if($employee->person)
                                                        <p>{{ $employee->person->last_name ?? '' }}</p>
                                                        <p>{{ $employee->person->first_name ?? '' }}</p>
                                                        <p>{{ $employee->person->patronymic ?? '' }}</p>
                                                    @else
                                                        <p>Нет персональных данных</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                                        class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить сотрудника
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Самостоятельные сотрудники --}}
                        @if($commissariat?->employeesIndependent()?->count() > 0)
                            <div class="department bg-[#BFBFBF] p-5 rounded-lg flex-shrink-0">
                                <h3 class="text-lg font-bold text-[#060606] mb-3">Самостоятельные сотрудники</h3>
                                <div class="employees grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @foreach ($commissariat?->employeesIndependent() as $employee)
                                        <div
                                            class="employee flex flex-col p-1.5 bg-[#565A5B] rounded-md h-full relative overflow-hidden">
                                            <a href="{{ route('employees.show', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                                class="node-info" aria-label="Подробнее">
                                                 <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                </svg>
                                            </a>
                                            <div class="employee__data">
                                                <div class="employee__fio text-left text-white text-xs">
                                                    @if($employee->getFullNameAttribute())
                                                        <p>{{ $employee->getFullNameAttribute() }}</p>
                                                    @else
                                                        <p>Нет персональных данных</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'is_independent' => 1, 'back_url' => url()->full()]) }}"
                                        class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить сотрудника
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Самостоятельные отделения --}}
                        @if($commissariat?->divisionsIntependent()?->count() > 0)
                            <div class="department bg-[#BFBFBF] p-5 rounded-lg flex-shrink-0">
                                <h3 class="text-lg font-bold text-[#060606] mb-3">Самостоятельные отделения</h3>
                                <div class="units grid grid-cols-1 md:grid-cols-2 gap-5">
                                    @foreach ($commissariat?->divisionsIntependent() as $division)
                                        <div class="unit bg-[#7F7F7F] p-2.5 rounded-lg">
                                            <div class="unit-title text-left text-white text-xl uppercase mb-2">
                                                {{ $division->name }}</div>

                                            {{-- Начальник отделения --}}
                                            <div
                                                class="node boss flex items-center justify-start gap-5 h-[100px] bg-[#060606] text-white p-5 rounded-lg text-left relative overflow-hidden">
                                                <a href="{{ route('divisions.show', ['id' => $division->id, 'back_url' => url()->full()]) }}"
                                                    class="node-info" aria-label="Подробнее">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                    </svg>
                                                </a>
                                                <div class="data">
                                                    @if(optional($division->getChiefAttribute())->getFullNameAttribute())
                                                        <p class="data__fio text-base text-white">
                                                            {{ optional($division->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                                        </p>
                                                    @else
                                                        <p>Не назначен начальник</p>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Сотрудники отделения --}}
                                            @if($division->employeePositions->count() > 0)
                                                <div class="employees grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5 mt-2.5">
                                                    @foreach ($division->employeePositions as $employeePosition)
                                                        <div
                                                            class="employee flex flex-col p-1.5 bg-[#565A5B] rounded-md h-full relative overflow-hidden">
                                                            <a href="{{ route('employees.show', ['id' => $employeePosition->employee->id, 'back_url' => url()->full()]) }}"
                                                                class="node-info" aria-label="Подробнее">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                    <circle cx="12" cy="12" r="10"></circle>
                                                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                                </svg>
                                                            </a>
                                                            <div class="employee__data">
                                                                <div class="employee__fio text-left text-white text-xs">
                                                                    @if(optional($employeePosition->employee)->getFullNameAttribute())
                                                                        <p>{{ optional($employeePosition->employee)->getFullNameAttribute() ?? "" }}
                                                                        </p>
                                                                    @else
                                                                        <p>Нет данных</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'division_id' => $division->id, 'back_url' => url()->full()]) }}"
                                                        class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Добавить сотрудника
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ route('employees.create', ['commissariat_id' => $commissariat->id, 'division_id' => $division->id, 'back_url' => url()->full()]) }}"
                                                    class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Добавить сотрудника
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                    <a href="{{ route('divisions.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                                        class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить самостоятельное отделение
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection