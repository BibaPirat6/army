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
    <div class="instruction-move">
        <a style="color: #000; text-decoration: underline;" href="{{ route('structure.index') }}">НАЗАД К СПИСКУ
            ВОЕНКОМАТОВ</a>
        Управление ~ <span style="color: chartreuse">КОЛЕСИКОМ МЫШИ</span> +
        Кнопка <span style="color: hotpink">ПРОБЕЛ</span> и <span style="color: hotpink">ЛЕВАЯ</span> кнопка мыши
    </div>


    <div id="viewport">
        <div id="canvas">
            <div class="tree">
                <div class="boss-wrapper">


                    <!-- Начальник комиссариата -->
                    <div class="node boss">
                        <div class="photo">
                            @if (isset($commissariat->chiefEmployee->person->photo))
                                <img src="{{ asset('storage/' . $commissariat->chiefEmployee->person->photo) }}"
                                    alt="Фото {{ $commissariat->chiefEmployee->person->last_name }}">
                            @else
                                <div class="no-photo">
                                    <p>Нет фото</p>
                                </div>
                            @endif
                        </div>

                        <div class="data">
                            <p class="data__title">Начальник комиссариата</p>
                            <p class="data__fio">{{ $commissariat->chiefEmployee->person->last_name ?? '' }}
                                {{ $commissariat->chiefEmployee->person->first_name ?? '' }}
                                {{ $commissariat->chiefEmployee->person->patronymic ?? '' }}
                            </p>
                            <div class="node-line"></div>
                            <div class="data__positions">
                                @if ($commissariat->chiefEmployee->positions->count() > 0)
                                    <p>Должности</p>
                                    <ul>
                                        @foreach ($commissariat->chiefEmployee->positions as $position)
                                            <li>{{ $position->position->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>Не назначены Должности</p>
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
                                <div class="node dept-title">{{ $department->name }}</div>
                                <div class="node head">
                                    <div class="photo">
                                        @if (isset($department->chiefEmployee->person->photo))
                                            <img src="{{ asset('storage/' . $department->chiefEmployee->person->photo) }}"
                                                alt="Фото {{ $department->chiefEmployee->person->last_name }}">
                                        @else
                                            <div class="no-photo">
                                                <p>Нет фото</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="data">
                                        <p class="title">Начальник отдела</p>
                                        <p class="fio"> {{ $department->chiefEmployee->person->last_name ?? '' }}
                                            {{ $department->chiefEmployee->person->first_name ?? '' }}
                                            {{ $department->chiefEmployee->person->patronymic ?? '' }}
                                        </p>
                                        <p class="position">Должности</p>
                                        @if ($department->chiefEmployee->positions->count() > 0)
                                            <ul>
                                                @foreach ($department->chiefEmployee->positions as $position)
                                                    <li>{{ $position->position->name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div>
                                                <span>Не назначены Должности</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>


                                {{-- Отделения --}}
                                @if ($department->divisions->count() > 0)
                                    <div class="units">
                                        @foreach ($department->divisions as $division)
                                            <div class="unit">
                                                <div class="unit-title">{{ $division->name }}</div>
                                                <div class="node head">
                                                    <p>Начальник отделения</p>
                                                    <p>
                                                        {{ $division->chiefEmployee->person->last_name ?? '' }}
                                                        {{ $division->chiefEmployee->person->first_name ?? '' }}
                                                        {{ $division->chiefEmployee->person->patronymic ?? '' }}
                                                    </p>

                                                    @if (isset($division->chiefEmployee->person->photo))
                                                        <img src="{{ asset('storage/' . $division->chiefEmployee->person->photo) }}"
                                                            alt="Фото {{ $division->chiefEmployee->person->last_name }}">
                                                    @else
                                                        <div>
                                                            <span>Нет фото</span>
                                                        </div>
                                                    @endif
                                                    @if ($division->chiefEmployee->positions->count() > 0)
                                                        <p>Должности</p>
                                                        <ul>
                                                            @foreach ($division->chiefEmployee->positions as $position)
                                                                <li>{{ $position->position->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <div>
                                                            <span>Не назначены Должности</span>
                                                        </div>
                                                    @endif

                                                </div>

                                                {{-- сотрудники отделения --}}
                                                <div class="employees">
                                                    @if ($division->employees->count() > 0)
                                                        @foreach ($division->employees as $employee)
                                                            <div class="employee">
                                                                <div class="photo">
                                                                    @if (isset($employee->person->photo))
                                                                        <img src="{{ asset('storage/' . $employee->person->photo) }}"
                                                                            alt="Фото {{ $employee->person->photo }}">
                                                                    @else
                                                                        <div>
                                                                            <span>Нет фото</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="data">
                                                                    <p class="title">Сотрудник</p>
                                                                    <p class="fio">
                                                                        {{ $employee->person->last_name ?? '' }}
                                                                        {{ $employee->person->first_name ?? '' }}
                                                                        {{ $employee->person->patronymic ?? '' }}
                                                                    </p>
                                                                    <p class="position">Должность</p>
                                                                    @if ($employee->positions->count() > 0)
                                                                        <ul>
                                                                            @foreach ($employee->positions as $position)
                                                                                <li>{{ $position->position->name }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @else
                                                                        <p>Не назначены Должности</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <p>Нет сотрудников</p>
                                                    @endif

                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach


                        {{-- сюда самостоятельных --}}

                        <div class="department">
                            <div class="divisions">
                                <p>Самостоятельные отделения</p>
                                @php
                                    $divisions = $commissariat->divisions()->whereNull('department_id')->get();
                                @endphp

                                @if ($divisions->count() > 0)
                                    @foreach ($divisions as $division)
                                        <div class="division">
                                            <div class="division__boss">
                                                <div class="photo">
                                                    @if (isset($division->chiefEmployee->person->photo))
                                                        <img src="{{ asset('storage/' . $division->chiefEmployee->person->photo) }}"
                                                            alt="Фото {{ $division->chiefEmployee->person->last_name }}">
                                                    @else
                                                        <div class="no-photo">
                                                            <p>Нет фото</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="data">
                                                    <p>Начальник отделения</p>
                                                    <p>{{ $division->chiefEmployee->person->last_name ?? '' }}
                                                        {{ $division->chiefEmployee->person->first_name ?? '' }}
                                                        {{ $division->chiefEmployee->person->patronymic ?? '' }}
                                                    </p>
                                                    <p>Должности</p>
                                                    @if ($division->chiefEmployee->positions->count() > 0)
                                                        <ul>
                                                            @foreach ($division->chiefEmployee->positions as $position)
                                                                <li>{{ $position->position->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p>Не назначены Должности</p>
                                                    @endif
                                                </div>
                                            </div>


                                            <div class="employees">
                                                @if ($division->employees->count() > 0)
                                                    @foreach ($division->employees as $employee)
                                                        <div class="employee">
                                                            <div class="photo">
                                                                @if (isset($employee->person->photo))
                                                                    <img src="{{ asset('storage/' . $employee->person->photo) }}"
                                                                        alt="Фото {{ $employee->person->photo }}">
                                                                @else
                                                                    <div>
                                                                        <span>Нет фото</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="data">
                                                                <p class="title">Сотрудник</p>
                                                                <p class="fio">
                                                                    {{ $employee->person->last_name ?? '' }}
                                                                    {{ $employee->person->first_name ?? '' }}
                                                                    {{ $employee->person->patronymic ?? '' }}
                                                                </p>
                                                                <p class="position">Должность</p>
                                                                @if ($employee->positions->count() > 0)
                                                                    <ul>
                                                                        @foreach ($employee->positions as $position)
                                                                            <li>{{ $position->position->name }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    <p>Не назначены Должности</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p>Нет сотрудников</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p>Нет самостоятельных отделений</p>
                                @endif
                            </div>
                        </div>

                        {{-- самостоятеьные сотрудники из другой организации --}}
                        <div class="department">
                            @php
                                $employees = $commissariat->getEmployeesWithoutRelations();
                            @endphp
                            @if ($employees->count() > 0)
                                <div class="employees">
                                    <p>Самостоятельные должности</p>
                                    @foreach ($employees as $employee)
                                        <div class="employee">
                                            <div class="photo">
                                                @if (isset($employee->person->photo))
                                                    <img src="{{ asset('storage/' . $employee->person->photo) }}"
                                                        alt="Фото {{ $employee->person->photo }}">
                                                @else
                                                    <div>
                                                        <span>Нет фото</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="data">
                                                <p class="title">Сотрудник</p>
                                                <p class="fio">
                                                    {{ $employee->person->last_name ?? '' }}
                                                    {{ $employee->person->first_name ?? '' }}
                                                    {{ $employee->person->patronymic ?? '' }}
                                                </p>
                                                <p class="position">Должность</p>
                                                @if ($employee->positions->count() > 0)
                                                    <ul>
                                                        @foreach ($employee->positions as $position)
                                                            <li>{{ $position->position->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p>Не назначены Должности</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Нет сотрудников</p>
                            @endif

                        </div>


                        {{-- Прямо зависят от начальника комиссариата --}}
                        <div class="department">
                            @php
                                $employees = $commissariat->getEmployeesRight();
                            @endphp
                            @if ($employees->count() > 0)
                                <div class="employees">
                                    <p>Зависят от нач. комиссариата</p>
                                    @foreach ($employees as $employee)
                                        <div class="employee">
                                            <div class="photo">
                                                @if (isset($employee->person->photo))
                                                    <img src="{{ asset('storage/' . $employee->person->photo) }}"
                                                        alt="Фото {{ $employee->person->photo }}">
                                                @else
                                                    <div>
                                                        <span>Нет фото</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="data">
                                                <p class="title">Сотрудник</p>
                                                <p class="fio">
                                                    {{ $employee->person->last_name ?? '' }}
                                                    {{ $employee->person->first_name ?? '' }}
                                                    {{ $employee->person->patronymic ?? '' }}
                                                </p>
                                                <p class="position">Должность</p>
                                                @if ($employee->positions->count() > 0)
                                                    <ul>
                                                        @foreach ($employee->positions as $position)
                                                            <li>{{ $position->position->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p>Не назначены Должности</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Нет сотрудников</p>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/structure.js') }}"></script>
@endpush
