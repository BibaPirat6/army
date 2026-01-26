@extends('layouts.main')

@section('header-title')
    Структура комиссариата
@endsection

@section('content')
    <div>
        <div>
            <div>
                <h1>{{ $commissariat->name }}</h1>
                @if ($commissariat->chiefEmployee)
                    <h5>
                        Начальник: <strong>{{ $commissariat->chiefEmployee->person->last_name }}
                            {{ $commissariat->chiefEmployee->person->first_name }}
                            {{ $commissariat->chiefEmployee->person->patronymic }}</strong>
                    </h5>
                @endif
                <div>
                    <h1>Самостоятельные должности</h1>
                    "
                </div>
            </div>
        </div>

        @forelse($commissariat->departments as $department)
            <div>
                <div>
                    <div>
                        <div>
                            <h4>{{ $department->name }}</h4>
                        </div>
                        <div>
                            @if ($department->chiefEmployee)
                                <span>
                                    Начальник: {{ $department->chiefEmployee->person->last_name }}
                                    {{ $department->chiefEmployee->person->first_name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div>
                    @forelse($department->divisions as $division)
                        <div>
                            <div>
                                <div>
                                    <div>
                                        <h5>{{ $division->name }}</h5>
                                    </div>
                                    <div>
                                        @if ($division->chiefEmployee)
                                            <span>
                                                Начальник: {{ $division->chiefEmployee->person->last_name }}
                                                {{ $division->chiefEmployee->person->first_name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div>
                                @php
                                    $employees = $division->employees();
                                @endphp
                                @if ($employees->count() > 0)
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ФИО</th>
                                                <th>Должность</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($employees as $employee)
                                                <tr>
                                                    <td>
                                                        {{ $employee->person->last_name }}
                                                        {{ $employee->person->first_name }}
                                                        {{ $employee->person->patronymic }}
                                                    </td>
                                                    <td>
                                                        @foreach ($employee->positions as $position)
                                                            <span>{{ $position->name }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @if ($employee->workStatus->name === 'active')
                                                            <span>Активный</span>
                                                        @else
                                                            <span>Неактивный</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>Нет сотрудников в отделении</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p>В отделе нет отделений</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div>
                В комиссариате нет отделов
            </div>
        @endforelse

        {{-- Отделения без отдела (если есть) --}}
        @php
            $divisionsWithoutDepartment = $commissariat->divisions()->whereNull('department_id')->get();
        @endphp
        @if ($divisionsWithoutDepartment->count() > 0)
            <div>
                <div>
                    <h4>Отделения (не привязаны к отделам)</h4>
                </div>
                <div>
                    @foreach ($divisionsWithoutDepartment as $division)
                        <div>
                            <div>
                                <div>
                                    <div>
                                        <h5>{{ $division->name }}</h5>
                                    </div>
                                    <div>
                                        @if ($division->chiefEmployee)
                                            <span>
                                                Начальник: {{ $division->chiefEmployee->person->last_name }}
                                                {{ $division->chiefEmployee->person->first_name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div>
                                @php
                                    $employees = $division->employees();
                                @endphp
                                @if ($employees->count() > 0)
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ФИО</th>
                                                <th>Должность</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($employees as $employee)
                                                <tr>
                                                    <td>
                                                        {{ $employee->person->last_name }}
                                                        {{ $employee->person->first_name }}
                                                        {{ $employee->person->patronymic }}
                                                    </td>
                                                    <td>
                                                        @foreach ($employee->positions as $position)
                                                            <span>{{ $position->name }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @if ($employee->workStatus->name === 'active')
                                                            <span>Активный</span>
                                                        @else
                                                            <span>Неактивный</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>Нет сотрудников в отделении</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
