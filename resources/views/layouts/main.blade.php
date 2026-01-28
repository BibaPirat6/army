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

<body style="background: #f4f0f0">
    <nav class="navigation relative z-[900]">
        <ul class="main-nav flex list-none m-0 p-0 gap-5 items-center bg-[#e7e1e1] p-3 px-5 border-b border-[#BFBFBF]">
            @if (auth()->check() && auth()->user()->role?->name === 'admin')
                <li class="group relative">
                    <span
                        class="dropdown-toggle cursor-pointer font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                        Сотрудники
                        <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </span>
                    <ul
                        class="dropdown-menu absolute top-full left-0 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[220px] z-[1000] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform -translate-y-2 group-hover:translate-y-0 backdrop-blur-sm">
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('employees.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Сотрудники
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('users.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Пользователи
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('persons.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Персональные данные
                            </a>
                        </li>
                        <li class="mb-0">
                            <a href="{{ route('work-statuses.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Рабочие статусы
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="group relative">
                    <span
                        class="dropdown-toggle cursor-pointer font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                        Структура
                        <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </span>
                    <ul
                        class="dropdown-menu absolute top-full left-0 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[240px] z-[1000] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform -translate-y-2 group-hover:translate-y-0 backdrop-blur-sm">
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('position-types.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Типы должностей
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('positions.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Должности
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('commissariats.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Комиссариаты
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('departments.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Отделы
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('divisions.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Отделения
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('employee-positions.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                НАЗНАЧИТЬ ДОЛЖНОСТЬ
                            </a>
                        </li>
                        <li class="mb-0">
                            <a href="{{ route('structure.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                СОЗДАНИЕ СТРУКТУРЫ
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <li>
                <a href="{{ route('home.index') }}"
                    class="font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644]">
                    Главная
                </a>
            </li>
            <li>
                <a href="{{ route('profile.index') }}"
                    class="font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644]">
                    Профиль
                </a>
            </li>

            <li class="ml-auto">
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                        class="font-bold text-[#060604] px-4 py-2 rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:shadow-md active:scale-[0.98]">
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
