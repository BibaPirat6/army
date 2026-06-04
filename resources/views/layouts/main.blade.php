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
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/android-chrome-192x192.png') }}">
    <!-- Theme color -->
    <meta name="msapplication-TileColor" content="#A60644">
    <meta name="theme-color" content="#ffffff">
    {{-- csrf --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('vite-resources')
    @stack('styles')

    <style>
        /* Адаптивная навигация - бургер появляется на 1400px */
        @media (max-width: 1400px) {

            /* Скрываем десктопное меню, показываем бургер */
            .desktop-nav {
                display: none !important;
            }

            .mobile-nav.active {
                display: flex !important;
            }

            /* Уменьшаем отступы и шрифты в мобильном меню */
            .mobile-nav {
                gap: 0.5rem !important;
            }

            .mobile-nav .nav-link {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .mobile-nav .nav-link span {
                font-size: 0.75rem !important;
            }

            /* Убираем тексты у Excel и Calendar, оставляем только иконки */
            .mobile-nav .excel-nav span,
            .mobile-nav .calendar-nav span {
                display: none !important;
            }

            .mobile-nav .excel-nav,
            .mobile-nav .calendar-nav {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
        }

        /* Дополнительное сжатие на очень маленьких экранах */
        @media (max-width: 768px) {
            .mobile-nav {
                gap: 0.25rem !important;
            }

            .mobile-nav .nav-link span {
                font-size: 0.7rem !important;
            }

            .mobile-nav .dropdown-btn span {
                font-size: 0.7rem !important;
            }
        }

        @media (max-width: 640px) {
            .mobile-nav .nav-link {
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }

            .mobile-nav .nav-link span {
                font-size: 0.65rem !important;
            }

            .mobile-nav .dropdown-btn {
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }
        }

        /* Стили для бургер-кнопки */
        .burger-btn {
            display: none;
        }

        @media (max-width: 1400px) {
            .burger-btn {
                display: block;
            }
        }

        /* Анимация для мобильного меню */
        .mobile-nav {
            transition: all 0.3s ease;
        }
    </style>
</head>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // ------------------ Dropdown logic ------------------
        const dropdowns = document.querySelectorAll(".dropdown-btn");

        dropdowns.forEach(btn => {
            const menu = btn.nextElementSibling;
            const arrow = btn.querySelector(".dropdown-arrow");

            btn.addEventListener("click", function(e) {
                e.stopPropagation();
                e.preventDefault();

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
            if (menu) {
                menu.addEventListener("click", function(e) {
                    e.stopPropagation();
                });
            }
        });

        // закрытие при клике вне
        document.addEventListener("click", function(e) {
            if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dropdown-btn')) {
                document.querySelectorAll(".dropdown-menu").forEach(m => {
                    m.classList.add("hidden", "opacity-0", "scale-95");
                });

                document.querySelectorAll(".dropdown-arrow").forEach(a => {
                    a.classList.remove("rotate-180");
                });
            }
        });

        // обработчик для отправки формы через Enter
        const filterInputs = document.querySelectorAll('.dropdown-menu input, .dropdown-menu select');
        filterInputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            });
        });

        // ------------------ Мобильное меню (бургер для экранов до 1400px) ------------------
        const burgerBtn = document.getElementById('burger-btn');
        const mobileNav = document.getElementById('mobile-nav');
        const burgerIcon = document.getElementById('burger-icon');
        const closeIcon = document.getElementById('close-icon');

        if (burgerBtn && mobileNav) {
            burgerBtn.addEventListener('click', function() {
                mobileNav.classList.toggle('active');
                burgerIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });

            // Закрывать меню при клике на любую ссылку в мобильной версии
            const navLinks = mobileNav.querySelectorAll('a, button[type="submit"]');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 1400) {
                        mobileNav.classList.remove('active');
                        burgerIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                    }
                });
            });

            // Закрывать меню при изменении размера окна с мобильного на десктоп
            window.addEventListener('resize', () => {
                if (window.innerWidth > 1400) {
                    mobileNav.classList.remove('active');
                    burgerIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                }
            });
        }
    });
</script>

<body style="background: #f4f0f0">

    <nav class="navigation fixed top-0 left-0 right-0 z-[900] bg-[#e7e1e1] border-b border-[#BFBFBF] shadow-lg">
        <div class="flex flex-wrap items-center justify-between px-3 xl:px-5 py-2 xl:py-3">

            <!-- Бургер-кнопка – появляется на экранах меньше 1400px -->
            <button id="burger-btn" class="burger-btn text-[#060606] hover:text-[#A60644] focus:outline-none p-1">
                <svg id="burger-icon" class="w-5 h-5 xl:w-6 xl:h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="close-icon" class="w-5 h-5 xl:w-6 xl:h-6 hidden" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Десктопное меню (показывается на экранах больше 1400px) -->
            <ul class="desktop-nav hidden xl:flex flex-row list-none m-0 p-0 gap-3 xl:gap-5 items-center w-auto">

                @if (auth()->check() && auth()->user()->role?->name === 'admin')
                    <li>
                        <a href="{{ route('persons-columns.index') }}"
                            class="nav-link font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            <span class="text-sm xl:text-base whitespace-nowrap">Персональные данные</span>
                        </a>
                    </li>

                    <li class="relative">
                        <button type="button"
                            class="dropdown-btn cursor-pointer font-bold text-[#060606] px-3 py-2 flex items-center gap-2 transition-colors duration-200 hover:text-[#A60644]">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <span class="text-sm xl:text-base">Должности</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-300 dropdown-arrow flex-shrink-0"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <ul
                            class="dropdown-menu absolute top-full left-0 mt-2 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 min-w-[240px] z-[1000]
                               hidden opacity-0 scale-95 transition-all duration-200">
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('commissariats.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span class="text-sm">Комиссариаты</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('departments.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span class="text-sm">Отделы</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('divisions.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span class="text-sm">Отделения</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('positions.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="text-sm">Должности</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('position-types.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                        </path>
                                    </svg>
                                    <span class="text-sm">Типы должностей</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('employees.index') }}"
                            class="nav-link font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="text-sm xl:text-base whitespace-nowrap">Сотрудники</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('structure.index') }}"
                        class="nav-link font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </svg>
                        <span class="text-sm xl:text-base whitespace-nowrap">Структура</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('excel-export.index') }}"
                        class="group relative flex items-center gap-2.5 px-4 py-2 rounded-lg
                           font-semibold tracking-wide
                           text-[#217346] bg-white/80 backdrop-blur-sm
                           border border-[#217346]/20
                           shadow-sm hover:shadow-md hover:shadow-[#217346]/10
                           transition-all duration-300 ease-out
                           hover:bg-[#217346] hover:text-white hover:border-[#217346]
                           hover:-translate-y-0.5
                           active:translate-y-0 active:shadow-sm">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"
                                stroke-width="2" />
                            <polyline points="14 2 14 8 20 8" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <rect x="7" y="12" width="10" height="8" rx="1" stroke-width="1.5" />
                            <line x1="12" y1="12" x2="12" y2="20" stroke-width="1.5" />
                            <line x1="7" y1="16" x2="17" y2="16" stroke-width="1.5" />
                        </svg>
                        <span>Excel Export</span>
                        <svg class="w-3.5 h-3.5 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </li>

                <li>
                    <a href="{{ route('calendar.index') }}"
                        class="group relative flex items-center gap-2.5 px-4 py-2 rounded-lg
                           font-semibold tracking-wide
                           text-[#EA580C] bg-white/80 backdrop-blur-sm
                           border border-[#3B82F6]/20
                           shadow-sm hover:shadow-md hover:shadow-[#3B82F6]/10
                           transition-all duration-300 ease-out
                           hover:bg-[#EA580C] hover:text-white hover:border-[#EA580C]
                           hover:-translate-y-0.5
                           active:translate-y-0 active:shadow-sm">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"
                                fill="none" />
                            <line x1="3" y1="9" x2="21" y2="9" stroke-width="2" />
                            <path d="M8 2v4" stroke-width="2" stroke-linecap="round" />
                            <path d="M16 2v4" stroke-width="2" stroke-linecap="round" />
                            <rect x="7" y="13" width="3" height="3" rx="0.5" fill="currentColor" />
                            <rect x="14" y="13" width="3" height="3" rx="0.5" fill="currentColor" />
                            <rect x="7" y="17" width="3" height="3" rx="0.5" fill="currentColor" />
                        </svg>
                        <span>Календарь</span>
                        <svg class="w-3.5 h-3.5 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </li>

                <li class="ml-auto">
                    <div class="flex flex-row gap-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="font-bold text-[#060604] px-4 py-2 rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:shadow-md active:scale-[0.98] flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                <span class="text-sm">Выйти</span>
                            </button>
                        </form>
                        <a href="{{ route('profile.index') }}"
                            class="font-bold text-[#060606] px-4 py-2 rounded-lg transition-all duration-200 hover:text-[#A60644] hover:bg-[#A60644]/5 flex items-center gap-2 whitespace-nowrap">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-sm">Профиль</span>
                        </a>
                    </div>
                </li>
            </ul>

            <!-- Мобильное меню (показывается на экранах до 1400px, скрыто по умолчанию) -->
            <ul id="mobile-nav"
                class="mobile-nav hidden flex-col list-none m-0 p-4 gap-2 items-stretch w-full mt-3
                   bg-[#e7e1e1] rounded-lg shadow-lg">

                @if (auth()->check() && auth()->user()->role?->name === 'admin')
                    <li>
                        <a href="{{ route('persons-columns.index') }}"
                            class="nav-link font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] hover:bg-[#A60644]/5 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            <span>Персональные данные</span>
                        </a>
                    </li>

                    <li class="relative">
                        <button type="button"
                            class="dropdown-btn cursor-pointer font-bold text-[#060606] px-3 py-2 flex items-center justify-between gap-2 transition-colors duration-200 hover:text-[#A60644] hover:bg-[#A60644]/5 rounded-lg w-full">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <span>Должности</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-300 dropdown-arrow flex-shrink-0"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <ul
                            class="dropdown-menu mt-1 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg shadow-xl list-none m-0 p-2 z-[1000]
                               hidden opacity-0 scale-95 transition-all duration-200">
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('commissariats.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span>Комиссариаты</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('departments.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span>Отделы</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('divisions.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span>Отделения</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('positions.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span>Должности</span>
                                </a>
                            </li>
                            <li class="mb-1 last:mb-0">
                                <a href="{{ route('position-types.index') }}"
                                    class="block px-4 py-2 text-[#060606] rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] hover:pl-5 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                        </path>
                                    </svg>
                                    <span>Типы должностей</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('employees.index') }}"
                            class="nav-link font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] hover:bg-[#A60644]/5 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                </path>
                            </svg>
                            <span>Сотрудники</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('structure.index') }}"
                        class="nav-link font-bold text-[#060606] px-3 py-2 block transition-colors duration-200 hover:text-[#A60644] hover:bg-[#A60644]/5 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-[#A60644]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </svg>
                        <span>Структура</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('excel-export.index') }}"
                        class="excel-nav group flex items-center justify-center gap-2 px-4 py-2 rounded-lg
                           font-semibold tracking-wide text-[#217346] bg-white/80 backdrop-blur-sm
                           border border-[#217346]/20 shadow-sm hover:shadow-md hover:shadow-[#217346]/10
                           transition-all duration-300 hover:bg-[#217346] hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"
                                stroke-width="2" />
                            <polyline points="14 2 14 8 20 8" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <rect x="7" y="12" width="10" height="8" rx="1" stroke-width="1.5" />
                            <line x1="12" y1="12" x2="12" y2="20" stroke-width="1.5" />
                            <line x1="7" y1="16" x2="17" y2="16" stroke-width="1.5" />
                        </svg>
                        <span>Excel Export</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('calendar.index') }}"
                        class="calendar-nav group flex items-center justify-center gap-2 px-4 py-2 rounded-lg
                           font-semibold tracking-wide text-[#EA580C] bg-white/80 backdrop-blur-sm
                           border border-[#3B82F6]/20 shadow-sm hover:shadow-md hover:shadow-[#3B82F6]/10
                           transition-all duration-300 hover:bg-[#EA580C] hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"
                                fill="none" />
                            <line x1="3" y1="9" x2="21" y2="9" stroke-width="2" />
                            <path d="M8 2v4" stroke-width="2" stroke-linecap="round" />
                            <path d="M16 2v4" stroke-width="2" stroke-linecap="round" />
                            <rect x="7" y="13" width="3" height="3" rx="0.5" fill="currentColor" />
                            <rect x="14" y="13" width="3" height="3" rx="0.5" fill="currentColor" />
                            <rect x="7" y="17" width="3" height="3" rx="0.5" fill="currentColor" />
                        </svg>
                        <span>Календарь</span>
                    </a>
                </li>

                <li class="border-t pt-3 mt-2">
                    <div class="flex flex-col gap-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="font-bold text-[#060604] px-4 py-2 rounded-lg transition-all duration-200 hover:bg-[#A60644]/10 hover:text-[#A60644] flex items-center justify-center gap-2 w-full">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                <span>Выйти</span>
                            </button>
                        </form>
                        <a href="{{ route('profile.index') }}"
                            class="font-bold text-[#060606] px-4 py-2 rounded-lg transition-all duration-200 hover:text-[#A60644] hover:bg-[#A60644]/5 flex items-center justify-center gap-2 w-full">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Профиль</span>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="pt-14 xl:pt-16"></div>

    @yield('content')
    @stack('scripts')
</body>

</html>
