@extends('layouts.main')

@section('header-title')
    Вход в систему
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('login.post') }}" method="POST">
        @csrf

        <label for="login">Логин</label>
        <input type="text" id="login" name="login" placeholder="Введите логин" value="{{ old('login') }}">
        @error('login')
            <span>{{ $message }}</span>
        @enderror

        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" placeholder="Введите пароль">
        @error('password')
            <span>{{ $message }}</span>
        @enderror

        <button type="submit">Войти</button>
    </form>
@endsection
