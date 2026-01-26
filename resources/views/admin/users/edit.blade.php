@extends('layouts.main')

@section('header-title')
    Пользователь {{ $user->login }}
@endsection

@section('content')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li> {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h1>Пользователь {{ $user->login }}</h1>

    <h3><a href="{{ $decodedBackUrl ? $decodedBackUrl : route("users.index") }}">Назад к списку</a></h3>

    <div>
        <h2>Изменить пользователя</h2>
        <form action="{{ route('users.update', $user->id) }}" method="post">
            @csrf
            @method('PUT')

            <label for="login">Логин*</label> <br>
            <input type="text" id="login" name="login" placeholder="Введите логин"
                value="{{ old('login', $user->login) }}"> <br>

            <label for="password">Пароль*</label> <br>
            <input type="text" id="password" name="password" placeholder="Введите пароль"
                value="{{ old('password') }}">
            <br>

            <label for="role">Роль</label> <br>
            <select name="role" id="role">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @if ($role->name == $user->role->name) selected @endif>
                        {{ $role->description }}
                    </option>
                @endforeach
            </select><br>

            <input type="hidden" name="employeeId" value="{{ $employeeId }}">
            <input type="hidden" name="decodedBackUrl" value="{{ $decodedBackUrl }}">

            <button type="submit">Изменить</button>
        </form>
    </div>

@endsection
