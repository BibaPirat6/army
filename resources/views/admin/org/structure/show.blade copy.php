@extends('layouts.main')

@section('header-title')
    {{ $commissariat->name }}
@endsection

@section('vite-resources')
    @vite(['resources/css/structure.css', 'resources/js/structure.js'])
@endsection


@section('content')
    <button id="resetView">Вернуться к центру</button>

    <a href="{{ route('structure.index') }}"
        class="absolute left-4 mt-2 inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200 z-[100]">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Назад
    </a>

    <div class="fixed top-20 right-5 z-50">
        <div class="relative">
            <!-- Кнопка для скрипта -->
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

            <!-- Меню для скрипта -->
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



    <div id="viewport">
        <div id="canvas">
            <div class="tree">
                <div class="boss-wrapper">


                    <!-- Начальник комиссариата -->
                    <div class="node boss">
                        <a href="{{ route('commissariats.show', [
        'id' => $commissariat->id,
        'back_url' => url()->full(),
    ]) }}" class="node-info" aria-label="Подробнее">
                            <!-- SVG иконка info -->
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100"
                                viewBox="0 0 50 50">
                                <path
                                    d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                </path>
                            </svg>
                        </a>

                        <div class="data">
                            @if (optional($commissariat->getChiefAttribute()))
                                <p class="data__fio">
                                    {{ optional($commissariat->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                </p>
                            @else
                                <p>Не назначен начальник</p>
                            @endif
                        </div>
                    </div>

                    {{-- линии --}}
                    <div class="lines-to-departments">
                        <div class="line vertical"></div>
                        <div class="line horizontal"></div>
                    </div>

                    <!-- ОТДЕЛЫ -->
                    <div class="departments">
                        @foreach ($commissariat->departments as $department)
                                            <div class="department">
                                                {{-- Данные по отделу --}}
                                                <div class="dept-title">{{ $department->name }}</div>
                                                <div class="node boss">
                                                    <a href="{{ route('departments.show', [
                                'id' => $department->id,
                                'back_url' => url()->full(),
                            ]) }}" class="node-info" aria-label="Подробнее">
                                                        <!-- SVG иконка info -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100"
                                                            viewBox="0 0 50 50">
                                                            <path
                                                                d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                    <div class="data">
                                                        @if (optional($department->getChiefAttribute()))
                                                            <p class="data__fio">
                                                                {{  optional($department->getChiefAttribute())->getFullNameAttribute() ?? ""  }}
                                                            </p>
                                                        @else
                                                            <p>Не назначен начальник отдела</p>
                                                        @endif
                                                    </div>

                                                </div>

                                                {{-- Отделения --}}
                                                @if ($department->divisions->count() > 0)
                                                                    <div class="units">
                                                                        @foreach ($department->divisions as $division)
                                                                                            <div class="unit">
                                                                                                <div class="unit-title">{{ $division->name }}</div>
                                                                                                <div class="node boss">
                                                                                                    <a href="{{ route('divisions.show', [
                                                                                                                'id' => $division->id,
                                                                                                                'back_url' => url()->full(),
                                                                                                            ]) }}" class="node-info"
                                                                                                        aria-label="Подробнее">
                                                                                                        <!-- SVG иконка info -->
                                                                                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100"
                                                                                                            viewBox="0 0 50 50">
                                                                                                            <path
                                                                                                                d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                                                            </path>
                                                                                                        </svg>
                                                                                                    </a>

                                                                                                   <div class="data">
                                                                                                        @if (optional($division->getChiefAttribute()))
                                                                                                            <p class="data__fio">
                                                                                                                {{  optional($division->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                                                                                            </p>
                                                                                                        @else 
                                                                                                            <p>Не назначен начальник</p>
                                                                                                        @endif

                                                                                                    </div>
                                                                                                </div>


                                                                                                {{-- сотрудники отделения --}}
                                                                                                @if ($division->employeePositions->count() > 0)
                                                                                                                    <div class="employees">
                                                                                                                        @foreach ($division->employeePositions as $employeePosition)
                                                                                                                                            <div class="employee">
                                                                                                                                                <a href="{{ route('employees.show', [
                                                                                                                                                            'id' => $employeePosition->employee->id,
                                                                                                                                                            'back_url' => url()->full(),
                                                                                                                                                        ]) }}"
                                                                                                                                                    class="node-info" aria-label="Подробнее">
                                                                                                                                                    <!-- SVG иконка info -->
                                                                                                                                                    <svg xmlns="
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            http://www.w3.org/2000/svg"
                                                                                                                                                        x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
                                                                                                                                                        <path
                                                                                                                                                            d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                                                                                                        </path>
                                                                                                                                                    </svg>
                                                                                                                                                </a>

                                                                                                                                                <div class="employee__data">
                                                                                                                                                        @if (optional($employeePosition->employee)->getFullNameAttribute())
                                                                                                                                                            <p class="data__fio">
                                                                                                                                                                {{  optional($employeePosition->employee)->getFullNameAttribute() ?? "" }}
                                                                                                                                                            </p>
                                                                                                                                                        @else 
                                                                                                                                                            <p>Не назначен начальник</p>
                                                                                                                                                        @endif
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                        @endforeach


                                                                                                                        <a href="{{ route('employees.create', [
                                                                                                                                    'commissariat_id' => $commissariat->id,
                                                                                                                                    'department_id' => $department->id,
                                                                                                                                    'division_id' => $division->id,
                                                                                                                                    'back_url' => url()->full(),
                                                                                                                                ]) }}"
                                                                                                                            class="
                                                                                                                                                                                                                                                                                                                                                                                                mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium
                                                                                                                                                                                                                                                                                                                                                                                                rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg
                                                                                                                                                                                                                                                                                                                                                                                                hover:shadow-xl active:scale-[0.98]">
                                                                                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                                                    d="M12 4v16m8-8H4"></path>
                                                                                                                            </svg>
                                                                                                                            Добавить сотрудника
                                                                                                                        </a>

                                                                                                                    </div>
                                                                                                @else
                                                                                                                    <a href="{{ route('employees.create', [
                                                                                                        'commissariat_id' => $commissariat->id,
                                                                                                        'department_id' => $department->id,
                                                                                                        'division_id' => $division->id,
                                                                                                        'back_url' => url()->full(),
                                                                                                    ]) }}"
                                                                                                                        class="
                                                                                                                                                                                                                                                                                                                                                                                        mt-2 inline-flex items-center px-6 py-3 bg-[#a37084] text-white font-medium rounded-lg
                                                                                                                                                                                                                                                                                                                                                                                        hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl
                                                                                                                                                                                                                                                                                                                                                                                        active:scale-[0.98]">
                                                                                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                                                d="M12 4v16m8-8H4"></path>
                                                                                                                        </svg>
                                                                                                                        Добавить сотрудника
                                                                                                                    </a>
                                                                                                @endif
                                                                        </div> @endforeach <a href="{{ route('divisions.create', [
                                                        'commissariat_id' => $commissariat->id,
                                                        'department_id' => $department->id,
                                                        'back_url' => url()->full(),
                                                    ]) }}"
                                                                            class=" inline-flex items-center px-6 py-3
                                                                                                                                                                                                                        bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors
                                                                                                                                                                                                                        duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                    d="M12 4v16m8-8H4"></path>
                                                                            </svg>
                                                                            Добавить отделение
                                                                        </a>
                                                                    </div>
                                                @else
                                                                    <a href="{{ route('divisions.create', [
                                                        'commissariat_id' => $commissariat->id,
                                                        'department_id' => $department->id,
                                                        'back_url' => url()->full(),
                                                    ]) }}"
                                                                        class=" mt-2 inline-flex items-center px-6 py-3
                                                                                                                                                                                                            bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors
                                                                                                                                                                                                            duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                d="M12 4v16m8-8H4"></path>
                                                                        </svg>
                                                                        Добавить отделение
                                                                    </a>
                                                @endif
                        </div> @endforeach {{-- сотрудники зависят от комиссара --}}
                        @if ($commissariat?->employeesNotIndependent()?->count() > 0)
                                            <div class="department">
                                                <h3>Сотрудники комиссариата</h3>
                                                <div class="employees">
                                                    @foreach ($commissariat?->employeesNotIndependent() as $employee)
                                                                            <div class="employee">
                                                                                <a href="{{ route('employees.show', [
                                                            'id' => $employee->id,
                                                            'back_url' => url()->full(),
                                                        ]) }}" class="node-info" aria-label="Подробнее">
                                                                                    <!-- SVG иконка info -->
                                                                                    <svg xmlns="
                                                                                                                                                                                                                                    http://www.w3.org/2000/svg"
                                                                                        x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
                                                                                        <path
                                                                                            d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                                        </path>
                                                                                    </svg>
                                                                                </a>


                                                                                <div class="employee__data">
                                                                                   <div class="employee__fio">
                                                                                        @if ($employee->person)
                                                                                            <p>{{ $employee->person->last_name ?? '' }}</p>
                                                                                            <p>{{ $employee->person->first_name ?? '' }}</p>
                                                                                            <p>{{ $employee->person->patronymic ?? '' }}</p>
                                                                                             @else <p>Нет персональных данных</p>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>


                                                                            </div>
                                                    @endforeach

                                                    <a href="{{ route('employees.create', [
                                'commissariat_id' => $commissariat->id,
                                'back_url' => url()->full(),
                            ]) }}"
                                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644]
                                                                                                                                            text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors
                                                                                                                                            duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Добавить сотрудника
                                                    </a>
                                                </div>
                                            </div>
                        @endif


                        {{-- самостоятельные сотрудники --}}
                        @if ($commissariat?->employeesIndependent()?->count() > 0)
                                            <div class="department">
                                                <h3>Самостоятельные сотрудники</h3>
                                                <div class="employees">
                                                    @foreach ($commissariat?->employeesIndependent() as $employee)
                                                                            <div class="employee">
                                                                                <a href="{{ route('employees.show', [
                                                            'id' => $employee->id,
                                                            'back_url' => url()->full(),
                                                        ]) }}" class="node-info" aria-label="Подробнее">
                                                                                    <!-- SVG иконка info -->
                                                                                    <svg xmlns="
                                                                                                                                                                                                                                    http://www.w3.org/2000/svg"
                                                                                        x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
                                                                                        <path
                                                                                            d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                                        </path>
                                                                                    </svg>
                                                                                </a>


                                                                                <div class="employee__data">
                                                                                    <div class="employee__fio">
                                                                                        @if ($employee->getFullNameAttribute())
                                                                                            <p>{{ $employee->getFullNameAttribute() }}</p>
                                                                                            @else <p>Нет персональных данных</p>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>


                                                                            </div>
                                                    @endforeach

                                                    <a href="{{ route('employees.create', [
                                'commissariat_id' => $commissariat->id,
                                'is_independent' => 1,
                                'back_url' => url()->full(),
                            ]) }}"
                                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644]
                                                                                                                                            text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors
                                                                                                                                            duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Добавить сотрудника
                                                    </a>
                                                </div>
                                            </div>
                        @endif


                        {{-- самостоятельные отделения --}}
                        @if ($commissariat?->divisionsIntependent()?->count() > 0)
                                            <div class="department">
                                                <h3>Самостоятельные отделения</h3>
                                                <div class="units">
                                                    @foreach ($commissariat?->divisionsIntependent() as $division)
                                                                            <div class="unit">
                                                                                <div class="unit-title">{{ $division->name }}</div>
                                                                                <div class="node boss">
                                                                                    <a href="{{ route('divisions.show', [
                                                            'id' => $division->id,
                                                            'back_url' => url()->full(),
                                                        ]) }}" class="node-info" aria-label="Подробнее">
                                                                                        <!-- SVG иконка info -->
                                                                                        <svg xmlns="
                                                                                                                                                                                                                                        http://www.w3.org/2000/svg"
                                                                                            x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
                                                                                            <path
                                                                                                d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                                            </path>
                                                                                        </svg>
                                                                                    </a>
                                                                                    <div class="data">
                                                                                        @if (optional($division->getChiefAttribute())->getFullNameAttribute())
                                                                                            <p class="data__fio">
                                                                                               {{  optional($division->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                                                                            </p>
                                                                                            @else <p>Не назначен начальник</p>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>


                                                                                {{-- сотрудники отделения --}}
                                                                                @if ($division->employeePositions->count() > 0)
                                                                                                        <div class="employees">
                                                                                                            @foreach ($division->employeePositions as $employeePosition)
                                                                                                                                    <div class="employee">
                                                                                                                                        <a href="{{ route('employees.show', [
                                                                                                                    'id' => $employeePosition->employee->id,
                                                                                                                    'back_url' => url()->full(),
                                                                                                                ]) }}"
                                                                                                                                            class="node-info" aria-label="Подробнее">
                                                                                                                                            <!-- SVG иконка info -->
                                                                                                                                            <svg xmlns="
                                                                                                                                                                                                                                                                                                                                                                                                                                                http://www.w3.org/2000/svg"
                                                                                                                                                x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
                                                                                                                                                <path
                                                                                                                                                    d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                                                                                                </path>
                                                                                                                                            </svg>
                                                                                                                                        </a>

                                                                                                                                        <div class="employee__data">
                                                                                                                                            <div class="employee__fio">
                                                                                                                                                @if (optional($employeePosition->employee)->getFullNameAttribute())
                                                                                                                                                    <p>{{ optional($employeePosition->employee)->getFullNameAttribute() ?? "" }}</p>
                                                                                                                                                     @else <p>Нет персональных данных</p>
                                                                                                                                                @endif
                                                                                                                                            </div>
                                                                                                                                        </div>


                                                                                                                                    </div>
                                                                                                            @endforeach

                                                                                                            <a href="{{ route('employees.create', [
                                                                                        'commissariat_id' => $commissariat->id,
                                                                                        'division_id' => $division->id,
                                                                                        'back_url' => url()->full(),
                                                                                    ]) }}"
                                                                                                                class="
                                                                                                                                                                                                                                                                                                                                                    mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium
                                                                                                                                                                                                                                                                                                                                                    rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg
                                                                                                                                                                                                                                                                                                                                                    hover:shadow-xl active:scale-[0.98]">
                                                                                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                                        d="M12 4v16m8-8H4"></path>
                                                                                                                </svg>
                                                                                                                Добавить сотрудника
                                                                                                            </a>
                                                                                                        </div>
                                                                                @else
                                                                                                        <a href="{{ route('employees.create', [
                                                                                        'commissariat_id' => $commissariat->id,
                                                                                        'division_id' => $division->id,
                                                                                        'back_url' => url()->full(),
                                                                                    ]) }}"
                                                                                                            class="
                                                                                                                                                                                                                                                                                                                                                mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium
                                                                                                                                                                                                                                                                                                                                                rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg
                                                                                                                                                                                                                                                                                                                                                hover:shadow-xl active:scale-[0.98]">
                                                                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                                    d="M12 4v16m8-8H4"></path>
                                                                                                            </svg>
                                                                                                            Добавить сотрудника
                                                                                                        </a>
                                                                                @endif
                                                    </div> @endforeach <a href="{{ route('divisions.create', [
                                'commissariat_id' => $commissariat->id,
                                'back_url' => url()->full(),
                            ]) }}"
                                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644]
                                                                                                                                            text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors
                                                                                                                                            duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
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