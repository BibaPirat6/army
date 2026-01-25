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

    <p><a href="{{ url()->previous() }}">Назад</a></p>

    <div>
        <p>ID {{ $user->id }}</p>
        <p>Логин {{ $user->login }}</p>
        <p>Создан {{ $user->created_at }}</p>
        <p>Обновлен {{ $user->updated_at ?? '---' }} </p>
        <p>Роль {{ $user->role->description ?? '---' }} </p>
    </div>

    <div>
        <h2>Изменить пользователя</h2>
        <form action="{{ route('users.update.post', $user->id) }}" method="post">
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

            <button type="submit">Изменить</button>
        </form>
    </div>

@endsection
