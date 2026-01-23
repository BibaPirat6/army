@extends('layouts.main')

@section('header-title')
    Создание назначения должности сотруднику
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
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
                    <img src="{{ asset('storage/' . $employee->person->photo) }}" alt="Фото пользователя"
                        style="max-width: 200px; max-height: 200px;">
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
        <?php
        echo '<pre>';
        print_r($employee->toarray());
        ?>
    </div>
@endsection
