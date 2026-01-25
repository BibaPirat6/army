@extends('layouts.main')

@section('header-title')
    Сотрудники
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if (session('success'))
        {{ session('success') }}
    @endif

    <h1>Сотрудники</h1>
    <h3><a href="{{ route('employees.create') }}">Создать сотрудника</a></h3>





    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
        @foreach ($employees as $employee)
            <div style="width: 20%; background-color: antiquewhite; padding: 10px;">
                <div>
                    <p>ID: {{ $employee->id }}</p>

                    <p><strong>ПОЛЬЗОВАТЕЛЬ:</strong></p>
                    @if ($employee->user)
                        <ul>
                            <li>ID: {{ $employee->user->id }}</li>
                            <li>Логин: {{ $employee->user->login }}</li>
                            <li>Роль: {{ $employee->user->role->description }}</li>
                            </li>
                            <li>
                                <a
                                    href="{{ route('users.edit', [
                                        'id' => $employee->user->id,
                                        'employee_id' => $employee->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}">
                                    Изменить
                                </a>
                            </li>
                            <li>
                                <form action="{{ route('users.delete', $employee->user->id) }}" method="post">
                                    @method('DELETE') @csrf
                                    <input type="hidden" name="backUrl" value="{{ route('employees.index') }}">
                                    <button>Удалить</button>
                                </form>
                            </li>
                        </ul>
                    @else
                        <p style="color: gray;">Пользователь не указан</p>
                        <p><a
                                href="{{ route('users.create', ['employee_id' => $employee->id, 'back_url' => route('employees.index')]) }}">
                                Создать
                            </a>
                        </p>
                    @endif

                    <p><strong>ПЕРСОНАЛЬНЫЕ ДАННЫЕ:</strong></p>
                    @if ($employee->person)
                        <ul>
                            <li>ID: {{ $employee->person->id }}</li>
                            <li>Имя: {{ $employee->person->first_name ?? '—' }}</li>
                            <li>Фамилия: {{ $employee->person->last_name ?? '—' }}</li>
                            <li>Отчество: {{ $employee->person->patronymic ?? '—' }}</li>
                            <li>Телефон: {{ $employee->person->phone ?? '—' }}</li>
                            <li>Email: {{ $employee->person->email ?? '—' }}</li>
                            <li><a
                                    href="{{ route('persons.edit', [
                                        'id' => $employee->person->id,
                                        'employee_id' => $employee->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}">Изменить</a>
                            </li>
                            <li>
                                <form action="{{ route('persons.delete', $employee->person->id) }}" method="post">
                                    @method('DELETE') @csrf
                                    <input type="hidden" name="backUrl" value="{{ route('employees.index') }}">
                                    <button>Удалить</button>
                                </form>
                            </li>
                        </ul>
                    @else
                        <p style="color: gray;">Персона не указана</p>
                        <p><a
                                href="{{ route('persons.create', [
                                    'employee_id' => $employee->id,
                                    'back_url' => route('employees.index'),
                                ]) }}">
                                Создать</a></p>
                    @endif

                    <p><strong>СТАТУС:</strong> {{ $employee->workStatus->description ?? '—' }}</p>
                </div>
                <div>
                    <p><a href="{{ route('employees.edit', $employee->id) }}">Редактировать</a></p>
                    <form action="{{ route('employees.delete', $employee->id) }}" method="post">@csrf
                        @method('DELETE')
                        <button type="submit">Удалить</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    @include('includes.pagination', ['paginator' => $employees])
@endsection
