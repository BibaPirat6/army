@extends('layouts.main')

@section('header-title')
    Создание
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif


    <h1>Создание</h1>

    <h3>
        <a href="{{ $decodedBackUrl ? $decodedBackUrl : route('users.index') }}">
            Назад к списку
        </a>
    </h3>

    <div>
        <h2>Создать пользователя</h2>
        <form action="{{ route('users.store') }}" method="post">
            @csrf
            <label for="login">Логин*</label> <br>
            <input type="text" placeholder="Введите логин" id="login" name="login" value="{{ old('login') }}"> <br>

            <label for="password">Пароль*</label> <br>
            <input type="text" placeholder="Введите пароль" id="password" name="password" value="{{ old('password') }}">
            <br>

            <label for="role">Роль</label> <br>
            <select name="role" id="role">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @if ($role->name == 'user') selected @endif>
                        {{ $role->description }}
                    </option>
                @endforeach
            </select><br>

            <input type="hidden" name="decodedBackUrl" value="{{ $decodedBackUrl }}">
            <input type="hidden" name="employeeId" value="{{ $employeeId }}">

            <button type="submit">Создать</button>
        </form>
    </div>

@endsection
