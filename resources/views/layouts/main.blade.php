<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('header-title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('vite-resources')
    <style>
        .navigation {
            z-index: 900;
            position: relative;
        }

        .main-nav {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
            align-items: center;
            background-color: #f8f9fa;
            padding: 10px 20px;
        }

        .main-nav>li {
            position: relative;
        }

        .main-nav a,
        .main-nav .dropdown-toggle {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            padding: 8px 12px;
            display: block;
            cursor: pointer;
        }

        .main-nav a:hover,
        .main-nav .dropdown-toggle:hover {
            color: #007bff;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            list-style: none;
            margin: 0;
            padding: 8px 0;
            min-width: 200px;
            z-index: 1000;
            display: none;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu li {
            margin: 0;
            padding: 0;
        }

        .dropdown-menu a {
            padding: 8px 16px;
            display: block;
            white-space: nowrap;
        }

        .dropdown-menu a:hover {
            background-color: #f1f1f1;
        }
    </style>

    @stack('styles')
</head>

<body>
    <nav class="navigation">
        <ul class="main-nav">
            @if (auth()->check() && auth()->user()->role?->name === 'admin')
                <li class="dropdown">
                    <span class="dropdown-toggle">Сотрудники 🔽</span>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('employees.index') }}">Сотрудники</a></li>
                        <li><a href="{{ route('users.index') }}">Пользователи</a></li>
                        <li><a href="{{ route('persons.index') }}">Персональные данные</a></li>
                        <li><a href="{{ route('work-statuses.index') }}">Рабочие статусы</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <span class="dropdown-toggle">Должности 🔽</span>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('position-types.index') }}">Типы должностей</a></li>
                        <li><a href="{{ route('positions.index') }}">Должности</a></li>
                        <li><a href="{{ route('commissariats.index') }}">Комиссариаты</a></li>
                        <li><a href="{{ route('departments.index') }}">Отделы</a></li>
                        <li><a href="{{ route('divisions.index') }}">Отделения</a></li>
                        <li><a href="{{ route('employee-positions.index') }}">НАЗНАЧИТЬ ДОЛЖНОСТЬ</a></li>
                        <li><a href="{{ route('structure.index') }}">СОЗДАНИЕ СТРУКТУРЫ</a></li>
                    </ul>
                </li>
            @endif

            <li><a href="{{ route('home.index') }}">Главная</a></li>
            <li><a href="{{ route('profile.index') }}">Профиль</a></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Выйти из аккаунта</button>
                </form>
            </li>
        </ul>
    </nav>

    @yield('content')

    @stack('scripts')
</body>

</html>
