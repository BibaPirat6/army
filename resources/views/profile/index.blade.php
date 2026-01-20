@extends('layouts.main')

@section('header-title')
    Профиль
@endsection

@section('content')
    @if (session('success'))
        {{ session('success') }}
    @endif

    <h1>Профиль</h1>

    <div>
        <p><b>Логин</b> {{ $employee->user?->login }}</p>
        <p><b>Роль</b> {{ $employee->user?->role?->description }}</p>

        <p><b>ФИО</b> {{ $employee->person?->last_name }} {{ $employee->person?->first_name }}
            {{ $employee->person?->patronymic }}</p>
        <p><b>Телефон</b> {{ $employee->person?->phone }}</p>
        <p><b>Почта</b> {{ $employee->person?->email }}</p>
        
        @if ($employee->person?->photo)
            <div>
                <img src="{{ asset('storage/' . $employee->person->photo) }}" alt="Фото пользователя"
                    style="max-width: 200px; max-height: 200px;">
            </div>
        @endif

        <p><a href="{{ route('profile.update.index') }}">Редактировать</a></p>
    </div>
@endsection
