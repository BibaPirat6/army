<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('header-title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    {{-- навигация --}}
    <nav>
        <ul>
            {{-- admin --}}
            @if (auth()->check() && auth()->user()->role?->name === 'admin')
                <li><b>Сотрудники</b></li>
                <li style="display: flex; justify-content: start; gap: 20px;">
                    <p style="width: auto"><a href="{{ route('employees.index') }}">Сотрудники</a></з>
                    <p style="width: auto"><a href="{{ route('users.index') }}">Пользователи</a></з>
                    <p style="width: auto"><a href="{{ route('persons.index') }}">Персональные данные</a></з>
                    <p style="width: auto"><a href="{{ route('work-statuses.index') }}">Рабочие статусы</a></з>
                </li>
                <li><b>Должности</b></li>
                <li style="display: flex; justify-content: start; gap: 20px;">
                    <p style="width: auto;"><a href="{{ route('position-types.index') }}">Типы должностей</a></p>
                    <p style="width: auto;"><a href="">Должности</a></p>
                    <p style="width: auto;"><a href="">Комиссариаты</a></p>
                    <p style="width: auto;"><a href="">Отделы</a></p>
                    <p style="width: auto;"><a href="">Отделения</a></p>
                    <p style="width: auto;"><a href="">НАЗНАЧИТЬ ДОЛЖНОСТЬ</a></p>
                    <p style="width: auto;"><a href="">СОЗДАНИЕ СТРУКТУРЫ</a></p>
                </li>
            @endif
            {{-- user --}}
            <li><a href="{{ route('home.index') }}">Главная</a></li>
            <li><a href="{{ route('profile.index') }}">Профиль</a></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">@csrf <button type="submit">Выйти из
                        аккаунта</button></form>
            </li>
        </ul>
    </nav>

    @yield('content')
</body>

</html>
