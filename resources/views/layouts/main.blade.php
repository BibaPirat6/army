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

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const dropdowns = document.querySelectorAll(".dropdown-btn");

        dropdowns.forEach(btn => {
            const menu = btn.nextElementSibling; // сразу следующий div
            const arrow = btn.querySelector(".dropdown-arrow");

            btn.addEventListener("click", function (e) {
                e.stopPropagation();
                e.preventDefault(); // Добавили preventDefault для надежности

                // закрыть все остальные dropdown
                document.querySelectorAll(".dropdown-menu").forEach(m => {
                    if (m !== menu) {
                        m.classList.add("hidden", "opacity-0", "scale-95");
                    }
                });

                document.querySelectorAll(".dropdown-arrow").forEach(a => {
                    if (a !== arrow) a.classList.remove("rotate-180");
                });

                // открыть/закрыть текущий
                menu.classList.toggle("hidden");
                setTimeout(() => {
                    menu.classList.toggle("opacity-0");
                    menu.classList.toggle("scale-95");
                }, 10);

                arrow.classList.toggle("rotate-180");
            });

            // чтобы клик внутри меню не закрывал его
            menu.addEventListener("click", function (e) {
                e.stopPropagation();
            });
        });

        // закрытие при клике вне
        document.addEventListener("click", function (e) {
            // Проверяем, что клик был не по кнопке submit и не по элементам формы
            if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dropdown-btn')) {
                document.querySelectorAll(".dropdown-menu").forEach(m => {
                    m.classList.add("hidden", "opacity-0", "scale-95");
                });

                document.querySelectorAll(".dropdown-arrow").forEach(a => {
                    a.classList.remove("rotate-180");
                });
            }
        });

        // Добавляем обработчик для отправки формы через Enter в input
        const filterInputs = document.querySelectorAll('.dropdown-menu input, .dropdown-menu select');
        filterInputs.forEach(input => {
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            });
        });

        // Для отладки - проверяем клики по кнопке submit
        document.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                console.log('Submit button clicked');
                // Не останавливаем propagation, чтобы форма отправилась
            });
        });

    });
</script>



<body style="background: #f4f0f0">
    <nav class="navigation fixed top-0 left-0 right-0 z-[900] bg-[#e7e1e1] border-b border-[#BFBFBF] shadow-lg">
        <ul class="main-nav flex list-none m-0 p-0 gap-5 items-center px-5 py-3">
            @if (auth()->check() && auth()->user()->role?->name === 'admin')
                {{-- <li class="relative">

                    <!-- Кнопка -->
                    <button type="button"
                        class="dropdown-btn cursor-pointer font-bold text-[#060606] px-3 py-2 flex items-center gap-1 transition-colors duration-200 hover:text-[#A60644]">

                        <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>

                        Данные

                        <svg class="w-4 h-4 transition-transform duration-300 dropdown-arrow" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>


                    <!-- Меню -->
                    <ul class="dropdown-menu absolute top-full left-0 mt-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[240px] z-[1000]
                       hidden opacity-0 scale-95 transition-all duration-200">

                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('users.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Пользователи
                            </a>
                        </li>

                        <li class="mb-1 last:mb-0">
                            <a href="{{ route('persons.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Перс. данные
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('work-statuses.index') }}"
                                class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5">
                                Рабочие статусы
                            </a>
                        </li>

                    </ul>
                </li> --}}

                <li>
                    <a href="{{ route('persons-columns.index') }}"
                        class="font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-1">
                        <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        Персональные данные
                    </a>
                </li>


                <li class="relative">

                    <!-- Кнопка -->
                    <button type="button"
                        class="dropdown-btn cursor-pointer font-bold text-[#060606] px-3 py-2 flex items-center gap-1 transition-colors duration-200 hover:text-[#A60644]">

                        <svg class="w-5 h-5 mr-1 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>

                        Должности

                        <svg class="w-4 h-4 transition-transform duration-300 dropdown-arrow" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <!-- Меню -->
                    <ul class="dropdown-menu absolute top-full left-0 mt-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[240px] z-[1000]
                       hidden opacity-0 scale-95 transition-all duration-200">

                        <!-- ТВОИ ПУНКТЫ МЕНЮ БЕЗ ИЗМЕНЕНИЙ -->
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


{{--
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const dropdowns = document.querySelectorAll(".dropdown-btn");

        dropdowns.forEach(btn => {

            const menu = btn.nextElementSibling;
            const arrow = btn.querySelector(".dropdown-arrow");

            btn.addEventListener("click", function (e) {
                e.stopPropagation();

                // закрыть все остальные
                document.querySelectorAll(".dropdown-menu").forEach(m => {
                    if (m !== menu) {
                        m.classList.add("hidden", "opacity-0", "scale-95");
                    }
                });

                document.querySelectorAll(".dropdown-arrow").forEach(a => {
                    if (a !== arrow) {
                        a.classList.remove("rotate-180");
                    }
                });

                // toggle текущего
                menu.classList.toggle("hidden");

                setTimeout(() => {
                    menu.classList.toggle("opacity-0");
                    menu.classList.toggle("scale-95");
                }, 10);

                arrow.classList.toggle("rotate-180");
            });
        });

        // клик вне меню
        document.addEventListener("click", function () {
            document.querySelectorAll(".dropdown-menu").forEach(m => {
                m.classList.add("hidden", "opacity-0", "scale-95");
            });

            document.querySelectorAll(".dropdown-arrow").forEach(a => {
                a.classList.remove("rotate-180");
            });
        });
    });
</script> --}}