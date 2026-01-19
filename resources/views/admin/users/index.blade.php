@extends('layouts.main')

@section('header-title')
    Пользователи
@endsection

@section('content')
    <h1>Пользователи</h1>


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

    <div>
        <h2>Создать пользователя</h2>
        <form action="{{ route('users.post') }}" method="post">
            @csrf
            <label for="login">Логин*</label> <br>
            <input type="text" placeholder="Введите логин" id="login" name="login" value="{{ old('login') }}"> <br>

            <label for="password">Пароль*</label> <br>
            <input type="text" placeholder="Введите пароль" id="password" name="password" value="{{ old('password') }}">
            <br>

            <button type="submit">Создать</button>
        </form>
    </div>


    <div>
        <ul style="display: flex; flex-wrap: wrap; gap:10px;">
            @foreach ($users as $user)
                <li style="background-color: antiquewhite; display: flex; width: 20%;">
                    <div>
                        <p>ID {{ $user->id }}</p>
                        <p>Логин {{ $user->login }}</p>
                        <p>Создан {{ $user->created_at }}</p>
                        <p>Обновлен {{ $user->updated_at ?? '---' }} </p>
                    </div>
                    <div>
                        <p><a href="{{ route('users.update.index', $user->id) }}">Изменить</a></p>
                        <form action="{{ route('users.delete', $user->id) }}" method="post">@csrf <button
                                type="submit">Удалить</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
