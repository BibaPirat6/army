@extends('layouts.main')

@section('header-title')
    Календарь задач
@endsection


@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">

        {{-- ============================================ --}}
        {{-- Верхняя панель управления --}}
        {{-- ============================================ --}}
        <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200/80 shadow-sm sticky top-0 z-30">
            <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">

                    {{-- Заголовок --}}
                    <div class="flex items-center gap-4 min-w-0">
                        <div
                            class="flex-shrink-0 p-2.5 bg-gradient-to-br from-slate-700 to-slate-900 rounded-xl shadow-lg shadow-slate-700/20">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                                Календарный график задач
                            </h1>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5 hidden sm:block">
                                Управление задачами и отслеживание загрузки личного состава
                            </p>
                        </div>
                    </div>

                    {{-- Кнопка "Статистика" --}}
                    <button onclick="window.openStatsModal()"
                        class="group relative inline-flex items-center gap-2.5 px-5 py-3 
               bg-gradient-to-br from-slate-700 to-slate-800 
               text-white text-sm font-semibold tracking-wide 
               rounded-xl border border-slate-600/30 
               shadow-lg shadow-slate-900/20 
               hover:from-slate-800 hover:to-slate-900 
               hover:shadow-xl hover:shadow-slate-900/30 
               hover:border-slate-500/40 
               focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 
               active:scale-[0.97] 
               transition-all duration-200 
               w-full sm:w-auto justify-center">

                        {{-- Иконка --}}
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>

                        {{-- Текст --}}
                        <span class="hidden sm:inline">Статистика</span>

                        {{-- Стрелка --}}
                        <svg class="hidden sm:block w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"
                            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- Основной контент: календарь --}}
        {{-- ============================================ --}}
        <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 py-6">

            {{-- Сообщение об ошибке --}}
            <div id="calendar-error"
                class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm 
                    flex items-center gap-3 animate-slide-down"
                role="alert">
                <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="calendar-error-message"></span>
            </div>

            {{-- Индикатор загрузки --}}
            <div id="calendar-loading"
                class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-50">
                <div
                    class="flex items-center gap-3 px-6 py-4 bg-white/95 backdrop-blur-sm rounded-2xl 
                        shadow-xl border border-gray-200/80">
                    <div class="relative">
                        <div class="w-6 h-6 border-3 border-slate-200 rounded-full"></div>
                        <div
                            class="absolute top-0 left-0 w-6 h-6 border-3 border-slate-700 rounded-full 
                                border-t-transparent animate-spin">
                        </div>
                    </div>
                    <span class="text-gray-700 font-medium text-sm">Загрузка данных...</span>
                </div>
            </div>

            {{-- Контейнер календаря --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden 
                    transition-all duration-300 hover:shadow-md">
                <div id="calendar" class="p-4 sm:p-6 lg:p-8"></div>
            </div>
        </div>
    </div>

    {{-- Модальное окно статистики --}}
    @include('admin.calendar.stats-modal')
@endsection

@push('styles')
    <style>
        /* ============================================ */
        /* Кнопка "Статистика" */
        /* ============================================ */
        .btn-stats {
            @apply inline-flex items-center gap-2 px-5 py-2.5 sm:py-3 bg-gradient-to-r from-slate-700 to-slate-800 text-white text-sm font-semibold rounded-xl hover:from-slate-800 hover:to-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-md shadow-slate-700/20 hover:shadow-lg hover:shadow-slate-700/30 active:scale-[0.98] active:shadow-sm;
        }

        /* ============================================ */
        /* Анимации */
        /* ============================================ */
        @keyframes slide-down {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-down {
            animation: slide-down 0.3s ease-out;
        }

        /* ============================================ */
        /* FullCalendar кастомизация */
        /* ============================================ */
        #calendar {
            /* Основные переменные */
            --fc-border-color: #e2e8f0;
            --fc-page-bg-color: #ffffff;
            --fc-neutral-bg-color: #f8fafc;
            --fc-today-bg-color: #f1f5f9;

            /* Кнопки навигации */
            --fc-button-bg-color: #334155;
            --fc-button-border-color: #334155;
            --fc-button-text-color: #ffffff;
            --fc-button-hover-bg-color: #1e293b;
            --fc-button-hover-border-color: #1e293b;
            --fc-button-active-bg-color: #0f172a;
            --fc-button-active-border-color: #0f172a;
            --fc-button-active-text-color: #ffffff;

            /* События */
            --fc-event-bg-color: #334155;
            --fc-event-border-color: #1e293b;
            --fc-event-text-color: #ffffff;

            --fc-small-font-size: 0.85em;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        /* ============================================ */
        /* Кнопки навигации FullCalendar */
        /* ============================================ */

        /* Все кнопки */
        .fc .fc-button {
            @apply font-semibold px-4 py-2 rounded-lg transition-all duration-200;
            text-transform: none !important;
            font-weight: 600 !important;
            letter-spacing: 0 !important;
        }

        /* Фокус */
        .fc .fc-button:focus {
            box-shadow: 0 0 0 3px rgba(51, 65, 85, 0.2) !important;
        }

        /* Кнопка "Сегодня" - особый акцент */
        .fc .fc-today-button {
            @apply shadow-md;
            background-color: #3730a3 !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        .fc .fc-today-button:hover {
            background-color: #1e293b !important;
            border-color: #1e293b !important;
        }

        .fc .fc-today-button:active,
        .fc .fc-today-button:disabled {
            background-color: #0f172a !important;
            border-color: #0f172a !important;
            opacity: 0.8;
        }

        /* Кнопки навигации (стрелки) */
        .fc .fc-prev-button,
        .fc .fc-next-button {
            background-color: #475569 !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }

        .fc .fc-prev-button:hover,
        .fc .fc-next-button:hover {
            background-color: #334155 !important;
            border-color: #334155 !important;
        }

        .fc .fc-prev-button:active,
        .fc .fc-next-button:active {
            background-color: #1e293b !important;
            border-color: #1e293b !important;
        }

        /* Кнопки переключения вида (год/месяц) */
        .fc .fc-multiMonthYear-button,
        .fc .fc-dayGridMonth-button {
            background-color: #64748b !important;
            border-color: #64748b !important;
            color: #ffffff !important;
        }

        .fc .fc-multiMonthYear-button:hover,
        .fc .fc-dayGridMonth-button:hover {
            background-color: #475569 !important;
            border-color: #475569 !important;
        }

        /* Активная кнопка вида */
        .fc .fc-multiMonthYear-button.fc-button-active,
        .fc .fc-dayGridMonth-button.fc-button-active {
            background-color: #0f172a !important;
            border-color: #0f172a !important;
            color: #ffffff !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.2) !important;
        }

        /* ============================================ */
        /* События календаря */
        /* ============================================ */
        .fc .fc-event {
            @apply rounded-lg border-0 shadow-sm cursor-pointer transition-all duration-200;
            padding: 4px 8px;
            font-weight: 500;
        }

        .fc .fc-event:hover {
            @apply shadow-md;
            transform: translateY(-1px);
            filter: brightness(1.15);
        }

        /* ============================================ */
        /* Заголовок календаря */
        /* ============================================ */
        .fc .fc-toolbar-title {
            @apply text-lg sm:text-xl lg:text-2xl font-bold text-gray-900;
        }

        /* Дни недели */
        .fc .fc-col-header-cell {
            @apply bg-slate-50 py-3 font-semibold text-slate-700 uppercase text-xs tracking-wider;
        }

        /* Ячейки дней */
        .fc .fc-daygrid-day {
            @apply transition-colors duration-150;
        }

        .fc .fc-daygrid-day:hover {
            @apply bg-slate-50;
        }

        /* Сегодняшний день */
        .fc .fc-day-today {
            @apply shadow-inner;
            background-color: #f1f5f9 !important;
        }

        /* ============================================ */
        /* Адаптивность */
        /* ============================================ */
        @media (max-width: 640px) {
            #calendar {
                padding: 0.75rem !important;
            }

            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.75rem;
            }

            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .fc .fc-button {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
            }

            .fc .fc-toolbar-title {
                font-size: 1rem;
                text-align: center;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .fc .fc-toolbar {
                flex-wrap: wrap;
            }
        }

        @media (min-width: 1921px) {
            #calendar {
                max-width: 1600px;
                margin: 0 auto;
            }
        }

        /* ============================================ */
        /* Кастомный скроллбар для контейнера */
        /* ============================================ */
        .fc .fc-scroller::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .fc .fc-scroller::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .fc .fc-scroller::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .fc .fc-scroller::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush

@push('scripts')
    <script>
        /**
         * Инициализация StatsModal
         * 
         * Выполняется в inline script для гарантии доступности
         * и независимости от сборки Vite
         */
        document.addEventListener('DOMContentLoaded', function() {
            // Данные статистики (передаются из контроллера)
            const taskStats = @json($taskStats ?? []);

            // DOM элементы
            const commissariatSearch = document.getElementById('statsCommissariatSearch');
            const commissariatList = document.getElementById('statsCommissariatList');
            const statsResult = document.getElementById('statsResult');
            const statsResultContent = document.getElementById('statsResultContent');
            const clearSearchBtn = document.getElementById('clearSearchBtn');
            const statsModal = document.getElementById('statsModal');

            let selectedCommissariat = null;
            let debounceTimer = null;

            /**
             * Экранирование HTML (защита от XSS)
             */
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            /**
             * Обновление данных статистики с сервера
             */
            async function refreshStatsData() {
                try {
                    const response = await fetch('/calendar/stats', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (response.ok) {
                        const freshData = await response.json();
                        // Обновляем глобальные данные
                        window._taskStats = freshData;

                        // Если модальное окно открыто и есть поисковый запрос, обновляем результаты
                        if (!statsModal.classList.contains('hidden') && commissariatSearch.value.trim()) {
                            const found = freshData.find(c => c.name === commissariatSearch.value);
                            if (found) {
                                showStatsResult(found);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Ошибка обновления статистики:', e);
                }
            }

            /**
             * Отрисовка списка комиссариатов
             */
            function renderCommissariatList(filter = '') {
                const data = window._taskStats || taskStats;
                const q = filter.toLowerCase().trim();

                commissariatList.innerHTML = '';

                const filtered = data.filter(c =>
                    !q || c.name.toLowerCase().includes(q)
                );

                if (filtered.length === 0) {
                    commissariatList.innerHTML = `
                <div class="px-4 py-6 text-center">
                    <div class="text-gray-400 mb-2">
                        <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">Ничего не найдено</p>
                </div>`;
                } else {
                    filtered.forEach(c => {
                        const li = document.createElement('li');
                        li.className =
                            'px-4 py-3 cursor-pointer hover:bg-slate-50 transition-colors duration-150 flex items-center justify-between border-b border-gray-100 last:border-b-0';

                        if (selectedCommissariat && selectedCommissariat.id === c.id) {
                            li.classList.add('bg-slate-50', 'border-l-4', 'border-l-slate-600');
                        }

                        li.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-800">${escapeHtml(c.name)}</span>
                    </div>
                    <div class="flex items-center gap-3 ml-3 shrink-0">
                        <span class="text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded-full font-medium">
                            ${escapeHtml(String(c.total))} задач
                        </span>
                    </div>`;

                        li.addEventListener('click', () => {
                            selectedCommissariat = c;
                            commissariatSearch.value = c.name;
                            commissariatList.classList.add('hidden');
                            showStatsResult(c);
                            updateClearButtonVisibility();
                        });

                        commissariatList.appendChild(li);
                    });
                }
            }

            /**
             * Показать результат выбранного комиссариата
             */
            function showStatsResult(c) {
                statsResult.classList.remove('hidden');

                let html = `
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-500 text-sm">Комиссариат</span>
                <span class="font-semibold text-gray-800">${escapeHtml(c.name)}</span>
            </div>
            <div class="border-t border-gray-200 my-2"></div>
            <div class="flex justify-between items-center py-2">
                <span class="font-semibold text-gray-700">Всего задач</span>
                <span class="text-2xl font-bold text-slate-700">${c.total}</span>
            </div>`;

                if (c.total > 0) {
                    html += `
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="/calendar/matrix/${c.id}" 
                       class="inline-flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-slate-700 to-slate-800 hover:from-slate-800 hover:to-slate-900 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md active:scale-[0.98]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Открыть матрицу сотрудников
                    </a>
                </div>`;
                }

                html += `
            <button onclick="clearStatsSelection()" 
                    class="mt-3 w-full px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                Выбрать другой комиссариат
            </button>`;

                statsResultContent.innerHTML = html;
            }

            /**
             * Очистить выбор
             */
            window.clearStatsSelection = function() {
                selectedCommissariat = null;
                commissariatSearch.value = '';
                statsResult.classList.add('hidden');
                statsResultContent.innerHTML = '';
                updateClearButtonVisibility();
                renderCommissariatList('');
                commissariatList.classList.remove('hidden');
                commissariatSearch.focus();
            };

            /**
             * Обновить видимость кнопки очистки
             */
            function updateClearButtonVisibility() {
                if (commissariatSearch.value.trim() !== '') {
                    clearSearchBtn.classList.remove('hidden');
                } else {
                    clearSearchBtn.classList.add('hidden');
                }
            }

            /**
             * Обработчик ввода с debounce
             */
            function handleSearchInput(e) {
                const value = e.target.value;
                updateClearButtonVisibility();
                statsResult.classList.add('hidden');
                statsResultContent.innerHTML = '';
                selectedCommissariat = null;

                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                }

                debounceTimer = setTimeout(() => {
                    renderCommissariatList(value);
                    commissariatList.classList.remove('hidden');
                }, 300);
            }

            // ============================================
            // Привязка событий
            // ============================================

            // Кнопка очистки
            clearSearchBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                window.clearStatsSelection();
            });

            // Поле поиска
            commissariatSearch.addEventListener('focus', () => {
                if (!statsResult.classList.contains('hidden')) return;
                commissariatList.classList.remove('hidden');
                renderCommissariatList(commissariatSearch.value);
                updateClearButtonVisibility();
            });

            commissariatSearch.addEventListener('input', handleSearchInput);

            // Клик вне списка
            document.addEventListener('click', (e) => {
                const block = document.getElementById('statsCommissariatBlock');
                if (block && !block.contains(e.target)) {
                    commissariatList.classList.add('hidden');
                }
            });

            // Закрытие по Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (!commissariatList.classList.contains('hidden')) {
                        commissariatList.classList.add('hidden');
                    } else if (!statsModal.classList.contains('hidden')) {
                        closeStatsModal();
                    }
                }
            });

            // ============================================
            // Глобальные функции для onclick
            // ============================================

            /**
             * Открыть модальное окно статистики
             */
            window.openStatsModal = function() {
                if (!statsModal) return;

                statsModal.classList.remove('hidden');
                statsModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';

                // Инициализируем данные
                window._taskStats = window._taskStats || taskStats;

                // Загружаем свежие данные с сервера
                refreshStatsData();

                // Показываем список
                commissariatSearch.value = '';
                selectedCommissariat = null;
                statsResult.classList.add('hidden');
                statsResultContent.innerHTML = '';
                updateClearButtonVisibility();
                renderCommissariatList('');
                commissariatList.classList.remove('hidden');

                // Фокус на поиск
                setTimeout(() => commissariatSearch.focus(), 100);
            };

            /**
             * Закрыть модальное окно статистики
             */
            window.closeStatsModal = function() {
                if (!statsModal) return;

                statsModal.classList.add('hidden');
                statsModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';

                // Очищаем состояние
                commissariatSearch.value = '';
                selectedCommissariat = null;
                statsResult.classList.add('hidden');
                statsResultContent.innerHTML = '';
                commissariatList.classList.add('hidden');
                updateClearButtonVisibility();
            };
        });
    </script>
@endpush
