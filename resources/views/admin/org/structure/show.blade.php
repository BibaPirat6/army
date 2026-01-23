@extends('layouts.main')

@section('header-title')
    Структура комиссариата
@endsection

@section('content')
    <h1>{{ $commissariat->name }}</h1>

    <h2>Отделы</h2>
    @if ($departments->count() > 0)
        <ul>
            @foreach ($departments as $department)
                <li>
                    <strong>{{ $department->name }}</strong>
                    @if (isset($departmentDivisions[$department->id]) && $departmentDivisions[$department->id]->count() > 0)
                        <ul>
                            <li>Отделения:</li>
                            <ul>
                                @foreach ($departmentDivisions[$department->id] as $division)
                                    <li>{{ $division->name }}
                                        @if ($division->specialization)
                                            ({{ $division->specialization }})
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </ul>
                    @endif
                    @if (isset($departmentEmployees[$department->id]) && $departmentEmployees[$department->id]->count() > 0)
                        <ul>
                            <li>Сотрудники:</li>
                            <ul>
                                @foreach ($departmentEmployees[$department->id] as $employee)
                                    <li>
                                        {{ $employee->person->first_name ?? '' }} {{ $employee->person->last_name ?? '' }}
                                    </li>
                                @endforeach
                            </ul>
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p>Нет отделов</p>
    @endif

    <h2>Отделения (независимые)</h2>
    @if ($divisions->count() > 0)
        <ul>
            @foreach ($divisions as $division)
                <li>{{ $division->name }}
                    @if ($division->specialization)
                        ({{ $division->specialization }})
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p>Нет отделений</p>
    @endif

    <h2>Сотрудники (прямые)</h2>
    @if ($employees->count() > 0)
        <ul>
            @foreach ($employees as $employee)
                <li>
                    {{ $employee->person->first_name ?? '' }} {{ $employee->person->last_name ?? '' }}
                </li>
            @endforeach
        </ul>
    @else
        <p>Нет сотрудников</p>
    @endif
@endsection
