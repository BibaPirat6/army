@extends('layouts.main')

@section('header-title')
    Создание назначения должности сотруднику
@endsection

@section('content')
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div style="color: red;">{{ $error }}</div>
        @endforeach
    @endif

    <h1>Создание назначения должности сотруднику</h1>
    <p><a href="{{ route('employee-positions.index') }}">Назад к списку назначений</a></p>


    <div>
        <h3>Данные о сотруднике</h3>
        <h4>Пользователь</h4>
        <ul>
            <li>Логин {{ $employee->user->login ?? '' }}</li>
            <li>Роль {{ $employee->user->role->description ?? '' }}</li>
            <li>Создан {{ $employee->user->created_at ?? '' }}</li>
            <li>Обновлен {{ $employee->user->role->update_at ?? '' }}</li>
        </ul>
        <h4>Рабочий статус - {{ $employee->workStatus->description ?? '' }}</h4>
        <h4>Персональные данные</h4>
        <ul>
            <li>ФИО {{ $employee->person->last_name ?? '' }} {{ $employee->person->first_name ?? '' }}
                {{ $employee->person->patronymic ?? '' }}</li>
            <li>Телефон {{ $employee->person->phone }}</li>
            <li>Почта {{ $employee->person->email }}</li>
            <li>
                @if ($employee->person->photo)
                    <img src="{{ asset('storage/' . $employee->person->photo) }}" alt="Фото пользователя">
                @endif
            </li>
        </ul>
        <h4>Должности</h4>
        @foreach ($employee->positions as $position)
            <div>
                Должность: {{ $position->position->name ?? '' }} <br>
                Ставка: {{ $position->rate ?? '' }} <br>
                Назначена: {{ $position->created_at ?? '' }} <br>
                Категория: {{ $position->position->positionType->name ?? '' }} <br>
            </div>
            <hr>
        @endforeach


        <h2>Назначение новой должности</h2>
        <form action="{{ route('employee-positions.store', $employee->id) }}" method="POST">
            @csrf
            <div>
                <label for="position_id">Должность:</label>
                <select name="position_id" id="position_id">
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="rate">Ставка:</label>
                <input type="text" name="rate" id="rate" placeholder="Введите ставку"
                    value="{{ old('rate', 1) }}">
            </div>

            <button type="submit">Назначить</button>
        </form>
    </div>
@endsection
