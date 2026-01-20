@extends('layouts.main')

@section('header-title')
    Редактирование профиля
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h1>Редактирование профиля</h1>

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
    </div>

    <div>
        <form action="{{ route('profile.update.post') }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <label for="login">Логин</label> <br>
            <input type="text" name="login" id="login" placeholder="Введите Логин"
                value="{{ old('login', $employee->user?->login) }}"> <br>

            <label for="last_name">Фамилия</label><br>
            <input type="text" name="last_name" id="last_name" placeholder="Введите Фамилию"
                value="{{ old('last_name', $employee->person?->last_name) }}"><br>
            <label for="first_name">Имя</label><br>
            <input type="text" name="first_name" id="first_name" placeholder="Введите Имя"
                value="{{ old('first_name', $employee->person?->first_name) }}"><br>
            <label for="patronymic">Отчество</label><br>
            <input type="text" name="patronymic" id="patronymic" placeholder="Введите Отчество"
                value="{{ old('patronymic', $employee->person?->patronymic) }}"><br>

            <label for="email">Почта</label><br>
            <input type="email" name="email" id="email" placeholder="Введите Почту"
                value="{{ old('email', $employee->person?->email) }}"><br>
            <label for="phone">Телефон</label><br>
            <input type="tel" name="phone" id="phone" placeholder="Введите Телефон"
                value="{{ old('phone', $employee->person?->phone) }}"><br>


            <label for="photo">Фото</label><br>
            <input type="file" name="photo" id="photo" placeholder="Введите Фото"><br>


            <button type="submit">Редактировать</button>
        </form>
    </div>
@endsection
