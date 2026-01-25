@extends('layouts.main')

@section('header-title')
    Персональные данные {{ $person->last_name }} {{ $person->first_name }} {{ $person->patronymic }}
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h1>Персональные данные {{ $person->last_name }} {{ $person->first_name }} {{ $person->patronymic }}</h1>
    <h3><a href="{{ route("persons.index") }}">Назад к списку</a></h3>

    <div>
        <p><b>Фамилия</b> {{ $person->last_name }}</p>
        <p><b>Имя</b> {{ $person->first_name }}</p>
        <p><b>Отчество</b> {{ $person->patronymic }}</p>
        <p><b>Email</b> {{ $person->email }}</p>
        <p><b>Телефон</b> {{ $person->phone }}</p>
        @if ($person->photo)
            <div>
                <img src="{{ asset('storage/' . $person->photo) }}" alt="Фото пользователя"
                    style="max-width: 200px; max-height: 200px;">
            </div>
        @endif
    </div>

    <div>
        <form action="{{ route('persons.update', $person->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <label for="last_name">Фамилия</label><br>
            <input type="text" name="last_name" id="last_name" placeholder="Введите Фамилию"
                value="{{ old('last_name', $person->last_name) }}"><br>
            <label for="first_name">Имя</label><br>
            <input type="text" name="first_name" id="first_name" placeholder="Введите Имя"
                value="{{ old('first_name', $person->first_name) }}"><br>
            <label for="patronymic">Отчество</label><br>
            <input type="text" name="patronymic" id="patronymic" placeholder="Введите Отчество"
                value="{{ old('patronymic', $person->patronymic) }}"><br>

            <label for="email">Почта</label><br>
            <input type="email" name="email" id="email" placeholder="Введите Почту"
                value="{{ old('email', $person->email) }}"><br>
            <label for="phone">Телефон</label><br>
            <input type="tel" name="phone" id="phone" placeholder="Введите Телефон"
                value="{{ old('phone', $person->phone) }}"><br>

            <label for="photo">Фото</label><br>
            <input type="file" name="photo" id="photo" placeholder="Введите Фото"><br>


            <button type="submit">Обновить</button>
        </form>
    </div>


@endsection
