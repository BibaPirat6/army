<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('header-title')</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/android-chrome-192x192.png') }}">
    <!-- Theme color -->
    <meta name="msapplication-TileColor" content="#A60644">
    <meta name="theme-color" content="#ffffff">

    {{-- styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('vite-resources')
    @stack('styles')
</head>

<body style="background: #f4f0f0">
    <nav class="navigation fixed top-0 left-0 right-0 z-[900] bg-[#e7e1e1] border-b border-[#BFBFBF] shadow-lg">
        <ul class="main-nav flex list-none m-0 p-0 gap-5 items-center px-5 py-3">
            @if (auth()->check() && auth()->user()->role?->name === 'admin')
                <li class="group relative">
                    <span
                        class="dropdown-toggle cursor-pointer font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                        <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Данные
                        <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </span>
                    <ul
                        class="dropdown-menu absolute top-full left-0 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[220px] z-[1000] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform -translate-y-2 group-hover:translate-y-0 backdrop-blur-sm">
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('users.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Пользователи
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('persons.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2">
                                    </path>
                                </svg>
                                Перс. данные
                            </a>
                        </li>
                        <li class="mb-0">
                            <a href="{{ route('work-statuses.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                Рабочие статусы
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="group relative">
                    <span
                        class="dropdown-toggle cursor-pointer font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                        <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        Должности
                        <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </span>
                    <ul
                        class="dropdown-menu absolute top-full left-0 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[240px] z-[1000] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform -translate-y-2 group-hover:translate-y-0 backdrop-blur-sm">
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('commissariats.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Комиссариаты
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('departments.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Отделы
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('divisions.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Отделения
                            </a>
                        </li>

                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('employee-positions.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Назначения
                            </a>
                        </li>
                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('positions.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Должности
                            </a>
                        </li>

                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('position-types.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                Типы должностей
                            </a>
                        </li>
                    </ul>
                </li>



                <li>
                    <a href="{{ route('employees.index') }}"
                        class="font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        Сотрудники
                    </a>
                </li>
            @endif



            <li>
                <a href="{{ route('structure.index') }}"
                    class="font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Структура
                </a>
            </li>





            <li class="ml-auto">
                <ul class="flex justify-end items-center">
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit"
                                class="font-bold text-[#060604] px-4 py-2 rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:shadow-md active:scale-[0.98] flex items-center gap-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                Выйти
                            </button>
                        </form>
                    </li>
                    <li>
                        <a href="{{ route('profile.index') }}"
                            class="font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Профиль
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </nav>

    <div class="pt-16"></div>

    @yield('content')
    @stack('scripts')
</body>

</html>
