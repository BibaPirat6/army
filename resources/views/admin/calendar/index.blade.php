@extends('layouts.main')

@section('header-title')
    Календарь
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Календарный график задач</h2>
        <div id="calendar"></div>
    </div>

    {{-- Модальное окно --}}
    <div id="taskModal" class="fixed inset-0 z-[999] hidden overflow-y-auto" aria-hidden="true">
        {{-- Оверлей --}}
        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" data-modal-overlay></div>

        {{-- Контейнер модалки --}}
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-800" id="modalTitle">Новая задача</h3>
                    <button type="button" data-modal-close
                        class="text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Ссылка на задачу (показывается только при редактировании) --}}
                <div id="taskLinkContainer" class="mb-4 hidden">
                    <a href="#" id="taskLink"
                        class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Перейти к задаче
                    </a>
                </div>

                <form id="taskForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="task_id" name="id">

                    {{-- Название --}}
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                        <input type="text" id="title" name="title" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Описание --}}
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                        <textarea id="description" name="description" rows="2"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    {{-- Ответственный с поиском --}}
                    <div class="relative mb-4" id="responsible-select">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Ответственный *
                        </label>

                        {{-- Видимое поле поиска --}}
                        <input type="text" id="employee_position_search" placeholder="Начните вводить ФИО или должность"
                            autocomplete="off" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                                   focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none">

                        {{-- Скрытое поле с ID --}}
                        <input type="hidden" name="employee_position_id" id="employee_position_id">

                        {{-- Выпадающий список --}}
                        <ul id="employee_position_list" class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF]
                                                   rounded-lg max-h-72 overflow-auto hidden shadow-lg">

                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать">
                                Очистить
                            </li>

                            @foreach($employeePositions as $ep)
                                @php
                                    $cp = $ep->commissariatPosition;
                                    $unitName = $cp->division?->name
                                        ?? $cp->department?->name
                                        ?? $cp->commissariat?->name
                                        ?? 'Не указано';
                                    $person = $ep->employee?->person;
                                    $fullName = $person
                                        ? trim($person->фамилия . ' ' . $person->имя . ' ' . ($person->отчество ?? ''))
                                        : 'Сотрудник #' . $ep->employee_id;
                                @endphp
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $ep->id }}"
                                    data-name="{{ $fullName }} — {{ $unitName }}"
                                    data-search="{{ mb_strtolower($fullName . ' ' . $unitName) }}">
                                    <span class="font-medium">{{ $fullName }}</span>
                                    <span class="text-gray-400 ml-1">— {{ $unitName }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Цвет + Квота --}}
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
                            <input type="color" id="color" name="color" value="#3788d8"
                                class="h-10 w-full rounded-lg border-gray-300 cursor-pointer">
                        </div>
                        <div>
                            <label for="quota" class="block text-sm font-medium text-gray-700 mb-1">Квота</label>
                            <input type="number" id="quota" name="quota" min="1" value="1"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Даты --}}
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Дата начала
                                *</label>
                            <input type="date" id="start_date" name="start_date" required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Дата
                                окончания</label>
                            <input type="date" id="end_date" name="end_date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Загрузка файлов --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Файлы</label>
                        <div id="taskFileDropzone"
                            class="dropzone border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition cursor-pointer">
                            <div class="dz-message text-gray-500 text-sm text-center">
                                Перетащите файлы сюда или кликните для выбора
                            </div>
                        </div>
                    </div>

                    {{-- Кнопки --}}
                    <div class="flex justify-end gap-3">
                        <button type="button" data-modal-close
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Отмена
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.openModal = function () {
            document.getElementById('taskModal').classList.remove('hidden');
        };

        window.closeModal = function () {
            document.getElementById('taskModal').classList.add('hidden');
        };

        window.resetForm = function () {
            document.getElementById('taskForm').reset();
            document.getElementById('task_id').value = '';
            document.getElementById('modalTitle').textContent = 'Новая задача';

            // Скрываем ссылку на задачу
            const taskLinkContainer = document.getElementById('taskLinkContainer');
            if (taskLinkContainer) {
                taskLinkContainer.classList.add('hidden');
            }

            if (typeof window.resetResponsibleSelect === 'function') {
                window.resetResponsibleSelect();
            }
        };

        document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                window.closeModal();
            });
        });

        const overlay = document.querySelector('[data-modal-overlay]');
        if (overlay) {
            overlay.addEventListener('click', function () {
                window.closeModal();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closeModal();
            }
        });
    </script>

    <script>
        // === ВЫПАДАЮЩИЙ СПИСОК С ПОИСКОМ ===
        (function () {
            const container = document.getElementById('responsible-select');
            if (!container) return;

            const input = container.querySelector('#employee_position_search');
            const hidden = container.querySelector('#employee_position_id');
            const list = container.querySelector('#employee_position_list');
            const items = list.querySelectorAll('li');

            function open() {
                list.classList.remove('hidden');
            }

            function close() {
                list.classList.add('hidden');
            }

            function filter(value) {
                const q = value.toLowerCase().trim();

                items.forEach(item => {
                    const name = (item.dataset.name || '').toLowerCase();
                    const search = (item.dataset.search || '').toLowerCase();
                    const id = item.dataset.id || '';

                    if (!q || name.includes(q) || search.includes(q) || id.includes(q)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            // Фокус — открываем
            input.addEventListener('focus', () => {
                open();
                filter(input.value);
            });

            // Ввод — сбрасываем ID и фильтруем
            input.addEventListener('input', () => {
                hidden.value = '';
                open();
                filter(input.value);
            });

            // Выбор элемента
            items.forEach(item => {
                item.addEventListener('click', () => {
                    const id = item.dataset.id || '';
                    const name = item.dataset.name || '';

                    input.value = name;
                    hidden.value = id;

                    close();
                });
            });

            // Клик вне — закрываем
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    close();
                }
            });

            window.setResponsibleSelect = function (id, name) {
                const input = document.getElementById('employee_position_search');
                const hidden = document.getElementById('employee_position_id');

                if (input && name) {
                    input.value = name;
                }
                if (hidden && id) {
                    hidden.value = id;
                }
            }

            window.resetResponsibleSelect = function () {
                const input = document.getElementById('employee_position_search');
                const hidden = document.getElementById('employee_position_id');

                if (input) input.value = '';
                if (hidden) hidden.value = '';
            };

        })();
    </script>
@endpush