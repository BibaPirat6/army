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
                                        @foreach ($commissariat->chiefEmployeePositions as $position)
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
                                <div class="node dept-title">{{ $department->name }}</div>
                                <div class="node boss">
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
                                                            @if ($division->chiefEmployee && $division->chiefEmployee->positions && $division->chiefEmployee->positions->count() > 0)
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/structure.js') }}"></script>
@endpush
