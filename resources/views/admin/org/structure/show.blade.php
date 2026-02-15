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

    <div class="fixed top-20 right-5 z-[9999]">
        <div class="relative group">
            <button
                class="group/dropdown-toggle font-bold text-[#060606] px-4 py-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создание
                <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <ul
                class="absolute top-full right-0 mt-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[220px] z-[1000] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform -translate-y-2 group-hover:translate-y-0">
                <li class="mb-1 last:mb-0">
                    <a href="{{ route('departments.create', [
                        'commissariat_id' => $commissariat->id,
                        'back_url' => url()->full(),
                    ]) }}"
                        class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        Отдел
                    </a>
                </li>
                <li class="mb-1 last:mb-0">
                    <a href="{{ route('divisions.create', [
                        'commissariat_id' => $commissariat->id,
                        'back_url' => url()->full(),
                    ]) }}"
                        class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        Отделение
                    </a>
                </li>
                <li class="mb-0">
                    <a href="{{ route('employees.create', [
                        'commissariat_id' => $commissariat->id,
                        'back_url' => url()->full(),
                    ]) }}"
                        class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Сотрудник
                    </a>
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
                        ]) }}"
                            class="node-info" aria-label="Подробнее">
                            <!-- SVG иконка info -->
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100"
                                viewBox="0 0 50 50">
                                <path
                                    d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                </path>
                            </svg>
                        </a>

                        @if ($commissariat->chiefEmployeePosition?->employee?->person)
                            <div class="photo">
                                @if (isset($commissariat->chiefEmployee->person->photo))
                                    <img src="{{ asset('storage/' . $commissariat->chiefEmployee->person->photo) }}">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                        <circle cx="32" cy="18" r="10" fill="#e5e7eb" />
                                        <path d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z" fill="#d1d5db" />
                                    </svg>
                                @endif
                            </div>
                        @endif

                        <div class="data">
                            @if ($commissariat->chiefEmployeePosition?->employee?->person)
                                <p class="data__fio">{{ $commissariat->chiefEmployee->person->last_name ?? '' }}
                                    {{ $commissariat->chiefEmployee->person->first_name ?? '' }}
                                    {{ $commissariat->chiefEmployee->person->patronymic ?? '' }}
                                </p>
                                <div class="node-line"></div>
                                <div class="data__positions">
                                    @if ($commissariat->chiefEmployeePosition?->employee?->person)
                                        <ul>
                                            @foreach ($commissariat->chiefEmployee->positions as $position)
                                                <li>{{ $position->position->name }} {{ $position->rate }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p>Не назначены должности</p>
                                    @endif
                                </div>
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
                                    ]) }}"
                                        class="node-info" aria-label="Подробнее">
                                        <!-- SVG иконка info -->
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100"
                                            height="100" viewBox="0 0 50 50">
                                            <path
                                                d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                            </path>
                                        </svg>
                                    </a>
                                    @if ($department->chiefEmployeePosition?->employee?->person)
                                        <div class="photo">
                                            @if (isset($department->chiefEmployee->person->photo))
                                                <img
                                                    src="{{ asset('storage/' . $department->chiefEmployee->person->photo) }}">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                    <circle cx="32" cy="18" r="10" fill="#e5e7eb" />
                                                    <path d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                        fill="#d1d5db" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="data">
                                        @if ($department->chiefEmployeePosition?->employee?->person)
                                            <p class="data__fio">{{ $department->chiefEmployee->person->last_name ?? '' }}
                                                {{ $department->chiefEmployee->person->first_name ?? '' }}
                                                {{ $department->chiefEmployee->person->patronymic ?? '' }}
                                            </p>
                                            <div class="node-line"></div>
                                            <div class="data__positions">
                                                @if (
                                                    $department->chiefEmployee &&
                                                        $department->chiefEmployee->positions &&
                                                        $department->chiefEmployee->positions->count() > 0)
                                                    <ul>
                                                        @foreach ($department->chiefEmployee->positions as $position)
                                                            <li>{{ $position->position->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p>Не назначены должности</p>
                                                @endif
                                            </div>
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
                                                    ]) }}"
                                                        class="node-info" aria-label="Подробнее">
                                                        <!-- SVG иконка info -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                            width="100" height="100" viewBox="0 0 50 50">
                                                            <path
                                                                d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                            </path>
                                                        </svg>
                                                    </a>

                                                    @if ($division->chiefEmployeePosition?->employee?->person)
                                                        <div class="photo">
                                                            @if (isset($division->chiefEmployeePosition?->employee?->person))
                                                                <img
                                                                    src="{{ asset('storage/' . $division->chiefEmployee->person->photo) }}">
                                                            @else
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 64 64">
                                                                    <circle cx="32" cy="18" r="10"
                                                                        fill="#e5e7eb" />
                                                                    <path
                                                                        d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                        fill="#d1d5db" />
                                                                </svg>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="data">
                                                        @if ($division->chiefEmployeePosition?->employee?->person)
                                                            <p class="data__fio">
                                                                {{ $division->chiefEmployee->person->last_name ?? '' }}
                                                                {{ $division->chiefEmployee->person->first_name ?? '' }}
                                                                {{ $division->chiefEmployee->person->patronymic ?? '' }}
                                                            </p>
                                                            <div class="node-line"></div>
                                                            <div class="data__positions">
                                                                @if ($division->chiefEmployeePosition?->employee?->person)
                                                                    <ul>
                                                                        @foreach ($division->chiefEmployee->positions as $position)
                                                                            <li>{{ $position->position->name }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    <p>Не назначены должности</p>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <p>Не назначен начальник</p>
                                                        @endif

                                                    </div>
                                                </div>


                                                {{-- сотрудники отделения --}}
                                                @if ($division->employees->count() > 0)
                                                    <div class="employees">
                                                        @foreach ($division->employees->unique('id') as $employee)
                                                            <div class="employee">
                                                                <a href="{{ route('employee-positions.show', [
                                                                    'id' => $employee->id,
                                                                    'back_url' => url()->full(),
                                                                ]) }}"
                                                                    class="node-info" aria-label="Подробнее">
                                                                    <!-- SVG иконка info -->
                                                                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                                        width="100" height="100"
                                                                        viewBox="0 0 50 50">
                                                                        <path
                                                                            d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                        </path>
                                                                    </svg>
                                                                </a>

                                                                <div class="employee__data">
                                                                    @if ($employee->person)
                                                                        <div class="photo">
                                                                            @if (isset($employee->person->photo))
                                                                                <img
                                                                                    src="{{ asset('storage/' . $employee->person->photo) }}">
                                                                            @else
                                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                                    viewBox="0 0 64 64">
                                                                                    <circle cx="32" cy="18"
                                                                                        r="10" fill="#e5e7eb" />
                                                                                    <path
                                                                                        d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                                        fill="#d1d5db" />
                                                                                </svg>
                                                                            @endif
                                                                        </div>
                                                                    @endif

                                                                    <div class="employee__fio">
                                                                        @if ($employee->person)
                                                                            <p>{{ $employee->person->last_name ?? '' }}</p>
                                                                            <p>{{ $employee->person->first_name ?? '' }}
                                                                            </p>
                                                                            <p>{{ $employee->person->patronymic ?? '' }}
                                                                            </p>

                                                                            <div class="node-line"></div>

                                                                            <div class="employee__positions">
                                                                                @if ($employee->positions->count())
                                                                                    <ul>
                                                                                        @foreach ($employee->positions as $position)
                                                                                            <li>{{ $position->position->name }}
                                                                                                {{ $position->rate }}</li>
                                                                                        @endforeach
                                                                                    </ul>
                                                                                @else
                                                                                    <p>Не назначены должности</p>
                                                                                @endif
                                                                            </div>
                                                                        @else
                                                                            <p>Нет <br> персональных <br> данных</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach


                                                        <a href="{{ route('employees.create', [
                                                            'commissariat_id' => $commissariat->id,
                                                            'department_id' => $department->id,
                                                            'division_id' => $division->id,
                                                            'back_url' => url()->full(),
                                                        ]) }}"
                                                            class="mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                            <svg class="w-5 h-5 mr-2" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 4v16m8-8H4"></path>
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
                                                        class="mt-2 inline-flex items-center px-6 py-3 bg-[#a37084] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Добавить сотрудника
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach

                                        <a href="{{ route('divisions.create', [
                                            'commissariat_id' => $commissariat->id,
                                            'department_id' => $department->id,
                                            'back_url' => url()->full(),
                                        ]) }}"
                                            class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
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
                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Добавить отделение
                                    </a>
                                @endif
                            </div>
                        @endforeach



                        {{-- сотрудники зависят от комиссара --}}
                        @if ($commissariat?->employeesNotIndependent()?->count() > 0)
                            <div class="department">
                                <h3>Сотрудники комиссариата</h3>
                                <div class="employees">
                                    @foreach ($commissariat?->employeesNotIndependent() as $employee)
                                        <div class="employee">
                                            <a href="{{ route('employee-positions.show', [
                                                'id' => $employee->id,
                                                'back_url' => url()->full(),
                                            ]) }}"
                                                class="node-info" aria-label="Подробнее">
                                                <!-- SVG иконка info -->
                                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100"
                                                    height="100" viewBox="0 0 50 50">
                                                    <path
                                                        d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                    </path>
                                                </svg>
                                            </a>


                                            <div class="employee__data">
                                                @if ($employee->person)
                                                    <div class="photo">
                                                        @if (isset($employee->person->photo))
                                                            <img src="{{ asset('storage/' . $employee->person->photo) }}">
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                                <circle cx="32" cy="18" r="10"
                                                                    fill="#e5e7eb" />
                                                                <path
                                                                    d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                    fill="#d1d5db" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="employee__fio">
                                                    @if ($employee->person)
                                                        <p>{{ $employee->person->last_name ?? '' }}</p>
                                                        <p>{{ $employee->person->first_name ?? '' }}</p>
                                                        <p>{{ $employee->person->patronymic ?? '' }}</p>
                                                        <div class="node-line"></div>

                                                        <div class="employee__positions">
                                                            @if ($employee->positions->count())
                                                                <ul>
                                                                    @foreach ($employee->positions as $position)
                                                                        <li>{{ $position->position->name }}
                                                                            {{ $position->rate }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <p>Не назначены должности</p>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p>Нет персональных данных</p>
                                                    @endif
                                                </div>
                                            </div>


                                        </div>
                                    @endforeach

                                    <a href="{{ route('employees.create', [
                                        'commissariat_id' => $commissariat->id,
                                        'back_url' => url()->full(),
                                    ]) }}"
                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
                                            <a href="{{ route('employee-positions.show', [
                                                'id' => $employee->id,
                                                'back_url' => url()->full(),
                                            ]) }}"
                                                class="node-info" aria-label="Подробнее">
                                                <!-- SVG иконка info -->
                                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100"
                                                    height="100" viewBox="0 0 50 50">
                                                    <path
                                                        d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                    </path>
                                                </svg>
                                            </a>


                                            <div class="employee__data">
                                                @if ($employee->person)
                                                    <div class="photo">
                                                        @if (isset($employee->person->photo))
                                                            <img src="{{ asset('storage/' . $employee->person->photo) }}">
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                                <circle cx="32" cy="18" r="10"
                                                                    fill="#e5e7eb" />
                                                                <path
                                                                    d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                    fill="#d1d5db" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="employee__fio">
                                                    @if ($employee->person)
                                                        <p>{{ $employee->person->last_name ?? '' }}</p>
                                                        <p>{{ $employee->person->first_name ?? '' }}</p>
                                                        <p>{{ $employee->person->patronymic ?? '' }}</p>
                                                        <div class="node-line"></div>

                                                        <div class="employee__positions">
                                                            @if ($employee->positions->count())
                                                                <ul>
                                                                    @foreach ($employee->positions as $position)
                                                                        <li>{{ $position->position->name }}
                                                                            {{ $position->rate }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <p>Не назначены должности</p>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p>Нет персональных данных</p>
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
                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
                                                ]) }}"
                                                    class="node-info" aria-label="Подробнее">
                                                    <!-- SVG иконка info -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100"
                                                        height="100" viewBox="0 0 50 50">
                                                        <path
                                                            d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                        </path>
                                                    </svg>
                                                </a>

                                                @if ($division->chiefEmployeePosition?->employee?->person)
                                                    <div class="photo">
                                                        @if (isset($division->chiefEmployeePosition?->employee?->person?->photo))
                                                            <img
                                                                src="{{ asset('storage/' . $division->chiefEmployee->person->photo) }}">
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                                <circle cx="32" cy="18" r="10"
                                                                    fill="#e5e7eb" />
                                                                <path
                                                                    d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                    fill="#d1d5db" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="data">
                                                    @if ($division->chiefEmployeePosition?->employee?->person)
                                                        <p class="data__fio">
                                                            {{ $division->chiefEmployee->person->last_name ?? '' }}
                                                            {{ $division->chiefEmployee->person->first_name ?? '' }}
                                                            {{ $division->chiefEmployee->person->patronymic ?? '' }}
                                                        </p>
                                                        <div class="node-line"></div>
                                                        <div class="data__positions">
                                                            @if ($division->chiefEmployeePosition?->employee?->person)
                                                                <ul>
                                                                    @foreach ($division->chiefEmployee->positions as $position)
                                                                        <li>{{ $position->position->name }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <p>Не назначены должности</p>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p>Не назначен начальник</p>
                                                    @endif
                                                </div>
                                            </div>


                                            {{-- сотрудники отделения --}}
                                            @if ($division->employees->count() > 0)
                                                <div class="employees">
                                                    @foreach ($division->employees->unique('id') as $employee)
                                                        <div class="employee">
                                                            <a href="{{ route('employee-positions.show', [
                                                                'id' => $employee->id,
                                                                'back_url' => url()->full(),
                                                            ]) }}"
                                                                class="node-info" aria-label="Подробнее">
                                                                <!-- SVG иконка info -->
                                                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                                    width="100" height="100" viewBox="0 0 50 50">
                                                                    <path
                                                                        d="M 25 2 C 12.309295 2 2 12.309295 2 25 C 2 37.690705 12.309295 48 25 48 C 37.690705 48 48 37.690705 48 25 C 48 12.309295 37.690705 2 25 2 z M 25 4 C 36.609824 4 46 13.390176 46 25 C 46 36.609824 36.609824 46 25 46 C 13.390176 46 4 36.609824 4 25 C 4 13.390176 13.390176 4 25 4 z M 25 11 A 3 3 0 0 0 22 14 A 3 3 0 0 0 25 17 A 3 3 0 0 0 28 14 A 3 3 0 0 0 25 11 z M 21 21 L 21 23 L 22 23 L 23 23 L 23 36 L 22 36 L 21 36 L 21 38 L 22 38 L 23 38 L 27 38 L 28 38 L 29 38 L 29 36 L 28 36 L 27 36 L 27 21 L 26 21 L 22 21 L 21 21 z">
                                                                    </path>
                                                                </svg>
                                                            </a>

                                                            <div class="employee__data">
                                                                <div class="photo">
                                                                    @if (isset($employee->person->photo))
                                                                        <img
                                                                            src="{{ asset('storage/' . $employee->person->photo) }}">
                                                                    @else
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            viewBox="0 0 64 64">
                                                                            <circle cx="32" cy="18" r="10"
                                                                                fill="#e5e7eb" />
                                                                            <path
                                                                                d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                                fill="#d1d5db" />
                                                                        </svg>
                                                                    @endif
                                                                </div>

                                                                <div class="employee__fio">
                                                                    @if ($employee->person)
                                                                        <p>{{ $employee->person->last_name ?? '' }}</p>
                                                                        <p>{{ $employee->person->first_name ?? '' }}
                                                                        </p>
                                                                        <p>{{ $employee->person->patronymic ?? '' }}
                                                                        </p>
                                                                        <div class="node-line"></div>

                                                                        <div class="employee__positions">
                                                                            @if ($employee->positions->count())
                                                                                <ul>
                                                                                    @foreach ($employee->positions as $position)
                                                                                        <li>{{ $position->position->name }}
                                                                                            {{ $position->rate }}</li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            @else
                                                                                <p>Не назначены должности</p>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <p>Нет персональных данных</p>
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
                                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 4v16m8-8H4"></path>
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
                                                    class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Добавить сотрудника
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach

                                    <a href="{{ route('divisions.create', [
                                        'commissariat_id' => $commissariat->id,
                                        'back_url' => url()->full(),
                                    ]) }}"
                                        class=" mt-2 inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
