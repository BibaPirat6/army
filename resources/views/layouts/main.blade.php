<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('header-title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('vite-resources')
    @stack('styles')
</head>

<body>
    <nav class="navigation relative z-[900]">
        <ul class="main-nav flex list-none m-0 p-0 gap-5 items-center bg-gray-50 p-3 px-5">
            @if (auth()->check() && auth()->user()->role?->name === 'admin')
                <li class="group relative">
                    <span
                        class="dropdown-toggle cursor-pointer font-bold text-gray-800 px-3 py-2 block hover:text-blue-600">
                        Сотрудники 🔽
                    </span>
                    <ul
                        class="dropdown-menu absolute top-full left-0 bg-white border border-gray-300 rounded shadow-lg 
                          list-none m-0 p-2 min-w-[200px] z-[1000] opacity-0 invisible group-hover:opacity-100 
                          group-hover:visible transition-all duration-200 transform -translate-y-2 group-hover:translate-y-0">
                        <li><a href="{{ route('employees.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Сотрудники</a>
                        </li>
                        <li><a href="{{ route('users.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Пользователи</a>
                        </li>
                        <li><a href="{{ route('persons.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Персональные
                                данные</a></li>
                        <li><a href="{{ route('work-statuses.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Рабочие
                                статусы</a></li>
                    </ul>
                </li>


                <li class="group relative">
                    <span
                        class="dropdown-toggle cursor-pointer font-bold text-gray-800 px-3 py-2 block hover:text-blue-600">
                        Должности 🔽
                    </span>
                    <ul
                        class="dropdown-menu absolute top-full left-0 bg-white border border-gray-300 rounded shadow-lg 
                          list-none m-0 p-2 min-w-[200px] z-[1000] opacity-0 invisible group-hover:opacity-100 
                          group-hover:visible transition-all duration-200 transform -translate-y-2 group-hover:translate-y-0">
                        <li><a href="{{ route('position-types.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Типы
                                должностей</a></li>
                        <li><a href="{{ route('positions.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Должности</a>
                        </li>
                        <li><a href="{{ route('commissariats.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Комиссариаты</a>
                        </li>
                        <li><a href="{{ route('departments.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Отделы</a>
                        </li>
                        <li><a href="{{ route('divisions.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">Отделения</a>
                        </li>
                        <li><a href="{{ route('employee-positions.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">НАЗНАЧИТЬ
                                ДОЛЖНОСТЬ</a></li>
                        <li><a href="{{ route('structure.index') }}"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600 rounded">СОЗДАНИЕ
                                СТРУКТУРЫ</a></li>
                    </ul>
                </li>
            @endif


            <li><a href="{{ route('home.index') }}"
                    class="font-bold text-gray-800 px-3 py-2 block hover:text-blue-600">Главная</a></li>
            <li><a href="{{ route('profile.index') }}"
                    class="font-bold text-gray-800 px-3 py-2 block hover:text-blue-600">Профиль</a></li>

            <li>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                        class="font-bold text-gray-800 px-3 py-2 hover:text-blue-600 bg-transparent border-none cursor-pointer">
                        Выйти из аккаунта
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    @yield('content')
    @stack('scripts')
</body>

</html>
