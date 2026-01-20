<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('header-title')</title>
</head>

<body>
    {{-- навигация --}}
    <nav>
        <ul>
            {{-- admin --}}
            @if (auth()->check() && auth()->user()->role?->name === 'admin')
                <li><a href="{{ route('home.index') }}">Главная</a></li>
                <li><a href="{{ route('users.index') }}">Пользователи</a></li>
                <li><a href="{{ route('employees.index') }}">Сотрудники</a></li>
                @endif
            {{-- user --}}
            <li><a href="{{ route('profile.index') }}">Ваш профиль</a></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">@csrf <button type="submit">Выйти из
                        аккаунта</button></form>
            </li>
        </ul>
    </nav>

    @yield('content')
</body>

</html>
