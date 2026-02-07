@extends('layouts.main')

@section('header-title')
    Структура комиссариата
@endsection

@section('vite-resources')
    @vite(['resources/css/structure.css', 'resources/js/structure.js'])
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/structure.css') }}">
@endpush


@section('content')
    <button id="resetView">Вернуться к центру</button>

    <div id="viewport">
        <div id="canvas">
            <div class="tree">
                <div class="boss-wrapper">


                    <!-- Начальник комиссариата -->
                    <div class="node boss">
                        <a href="{{ route('commissariats.show', [
                            'id' => $commissariat->id,
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


                        <div class="photo">
                            @if (isset($commissariat->chiefEmployeePosition))
                                <img src="{{ asset('storage/' . $commissariat->chiefEmployee->person->photo) }}">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                    <circle cx="32" cy="18" r="10" fill="#e5e7eb" />
                                    <path d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z" fill="#d1d5db" />
                                </svg>
                            @endif
                        </div>
                        <div class="data">
                            @if (!$commissariat->chiefEmployeePosition?->employee?->person)
                                <p>Не назначен начальник</p>
                            @else
                                <p class="data__fio">{{ $commissariat->chiefEmployee->person->last_name ?? '' }}
                                    {{ $commissariat->chiefEmployee->person->first_name ?? '' }}
                                    {{ $commissariat->chiefEmployee->person->patronymic ?? '' }}
                                </p>
                            @endif
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

                                    <div class="photo">
                                        @if (isset($department->chiefEmployee->person->photo))
                                            <img src="{{ asset('storage/' . $department->chiefEmployee->person->photo) }}">
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                <circle cx="32" cy="18" r="10" fill="#e5e7eb" />
                                                <path d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                    fill="#d1d5db" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="data">
                                        @if (!$department->chiefEmployeePosition?->employee?->person)
                                            <p>Не назначен начальник</p>
                                        @else
                                            <p class="data__fio">{{ $department->chiefEmployee->person->last_name ?? '' }}
                                                {{ $department->chiefEmployee->person->first_name ?? '' }}
                                                {{ $department->chiefEmployee->person->patronymic ?? '' }}
                                            </p>
                                        @endif
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


                                                    <div class="photo">
                                                        @if (isset($division->chiefEmployeePosition?->employee?->person))
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
                                                    <div class="data">
                                                        @if (!$division->chiefEmployeePosition?->employee?->person)
                                                            <p>Не назначен начальник</p>
                                                        @else
                                                            <p class="data__fio">
                                                                {{ $division->chiefEmployee->person->last_name ?? '' }}
                                                                {{ $division->chiefEmployee->person->first_name ?? '' }}
                                                                {{ $division->chiefEmployee->person->patronymic ?? '' }}
                                                            </p>
                                                        @endif
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
                                                    </div>
                                                </div>


                                                {{-- сотрудники отделения --}}
                                                @if ($division->employees->count() > 0)
                                                    <div class="employees">
                                                        @foreach ($division->employees->unique('id') as $employee)
                                                            <div class="employee">
                                                                <a href="{{ route('employee-positions.show', [
                                                                    'id' => $employee->id,
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
                                                                        @if (!$employee->person)
                                                                            <p>Нет <br> персональных <br> данных</p>
                                                                        @else
                                                                            <p>{{ $employee->person->last_name }}</p>
                                                                            <p>{{ $employee->person->first_name }}</p>
                                                                            <p>{{ $employee->person->patronymic }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>

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
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p>Не назначены сотрудники</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p>Не назначены отделения</p>
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
                                                <div class="photo">
                                                    @if (isset($employee->person->photo))
                                                        <img src="{{ asset('storage/' . $employee->person->photo) }}">
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                            <circle cx="32" cy="18" r="10"
                                                                fill="#e5e7eb" />
                                                            <path d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                fill="#d1d5db" />
                                                        </svg>
                                                    @endif
                                                </div>

                                                <div class="employee__fio">
                                                    @if (!$employee->person)
                                                        <p>Нет <br> персональных <br> данных</p>
                                                    @else
                                                        <p>{{ $employee->person->last_name }}</p>
                                                        <p>{{ $employee->person->first_name }}</p>
                                                        <p>{{ $employee->person->patronymic }}</p>
                                                    @endif
                                                </div>
                                            </div>

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
                                        </div>
                                    @endforeach
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
                                                <div class="photo">
                                                    @if (isset($employee->person->photo))
                                                        <img src="{{ asset('storage/' . $employee->person->photo) }}">
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                            <circle cx="32" cy="18" r="10"
                                                                fill="#e5e7eb" />
                                                            <path d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                fill="#d1d5db" />
                                                        </svg>
                                                    @endif
                                                </div>

                                                <div class="employee__fio">
                                                    @if (!$employee->person)
                                                        <p>Нет <br> персональных <br> данных</p>
                                                    @else
                                                        <p>{{ $employee->person->last_name }}</p>
                                                        <p>{{ $employee->person->first_name }}</p>
                                                        <p>{{ $employee->person->patronymic }}</p>
                                                    @endif
                                                </div>
                                            </div>

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
                                        </div>
                                    @endforeach
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


                                                <div class="photo">
                                                    @if (isset($division->chiefEmployeePosition?->employee?->person))
                                                        <img
                                                            src="{{ asset('storage/' . $division->chiefEmployee->person->photo) }}">
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                                            <circle cx="32" cy="18" r="10"
                                                                fill="#e5e7eb" />
                                                            <path d="M32 36 C20 36 12 44 12 56 L52 56 C52 44 44 36 32 36 Z"
                                                                fill="#d1d5db" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="data">
                                                    @if (!$division->chiefEmployeePosition?->employee?->person)
                                                        <p>Не назначен начальник</p>
                                                    @else
                                                        <p class="data__fio">
                                                            {{ $division->chiefEmployee->person->last_name ?? '' }}
                                                            {{ $division->chiefEmployee->person->first_name ?? '' }}
                                                            {{ $division->chiefEmployee->person->patronymic ?? '' }}
                                                        </p>
                                                    @endif
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
                                                </div>
                                            </div>


                                            {{-- сотрудники отделения --}}
                                            @if ($division->employees->count() > 0)
                                                <div class="employees">
                                                    @foreach ($division->employees->unique('id') as $employee)
                                                        <div class="employee">
                                                            <a href="{{ route('employee-positions.show', [
                                                                'id' => $employee->id,
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
                                                                    @if (!$employee->person)
                                                                        <p>Нет <br> персональных <br> данных</p>
                                                                    @else
                                                                        <p>{{ $employee->person->last_name }}</p>
                                                                        <p>{{ $employee->person->first_name }}</p>
                                                                        <p>{{ $employee->person->patronymic }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>

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
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p>Не назначены сотрудники</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
