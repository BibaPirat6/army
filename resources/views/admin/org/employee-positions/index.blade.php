@extends('layouts.main')

@section('header-title')
    Назначение должностей сотрудникам
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <h1> Назначение должностей сотрудникам</h1>
    <div style="display: flex; justify-content: start; align-items: center; gap:10px;">
        <p><a href="{{ route('employees.index') }}">Сотрудники</a></p>
        <p><a href="{{ route('positions.index') }}">Должности</a></p>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;" border="1">
        <thead>
            <tr>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Почта</th>
                <th>Должности</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->person->last_name ?? ""}}
                        {{ $employee->person->first_name ?? ""}}
                        {{ $employee->person->patronymic ?? ""}}</td>
                    <td>{{ $employee->person->phone ?? ""}}</td>
                    <td>{{ $employee->person->email ?? ""}}</td>
                    <td>
                        @foreach ($employee->positions as $employeePosition)
                            <div>
                                Должность: {{ $employeePosition->position->name }}
                                Ставка: {{ $employeePosition->rate }}
                            </div>
                        @endforeach
                    </td>
                    <td>
                        <p><a href="{{ route('employee-positions.create', $employee->id) }}">Назначить должность</a></p>
                        <p><a href="{{ route('employee-positions.edit', $employee->id) }}">Редактировать</a></p>
                        <form action="{{ route('employee-positions.destroy', $employee->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Удалить все назначения</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
