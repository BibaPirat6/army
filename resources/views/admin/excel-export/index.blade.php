@extends('layouts.main')

@section('header-title')
    Экспорт в Excel
@endsection

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-4">
                <!-- Сотрудники -->
                <div class="bg-white border rounded-lg p-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="font-medium">Сотрудники</span>
                    </div>
                    <a href="{{ route('excel-export.employee') }}"
                        class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        Скачать
                    </a>
                </div>

                <!-- Структура -->
                <div class="bg-white border rounded-lg p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="font-medium">Структура комиссариата</span>
                    </div>

                    <form action="{{ route('excel-export.structure') }}" method="POST" id="structureForm">
                        @csrf

                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-visible">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-medium text-gray-900">Экспорт структуры и штатных должностей</h3>
                                <p class="text-sm text-gray-500 mt-1">Выберите уровень и элемент для экспорта</p>
                            </div>

                            <div class="p-6">
                                <!-- Выбор уровня -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Уровень экспорта *
                                    </label>
                                    <select name="level" id="level_select"
                                        class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                        <option value="">-- Выберите уровень --</option>
                                        <option value="commissariat">Комиссариат</option>
                                        <option value="department">Отдел</option>
                                        <option value="division">Отделение</option>
                                    </select>
                                </div>

                                <!-- Выбор комиссариата -->
                                <div class="mb-6" id="commissariat_block" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Комиссариат *
                                    </label>
                                    <div class="relative" style="z-index: 30;">
                                        <input type="text" id="commissariat_search"
                                            placeholder="Введите название комиссариата"
                                            class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                            autocomplete="off">
                                        <input type="hidden" name="commissariat_id" id="commissariat_id">
                                        <div id="commissariat_list"
                                            class="absolute z-50 mt-1 w-full md:w-80 bg-white border border-gray-300 rounded-lg shadow-lg max-h-72 overflow-auto hidden">
                                            <div class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500 border-b"
                                                data-id="" data-name="" data-static="true">
                                                ✕ Очистить
                                            </div>
                                            @foreach ($commissariats as $commissariat)
                                                <div class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                                    data-id="{{ $commissariat->id }}" data-name="{{ $commissariat->name }}">
                                                    {{ $commissariat->name }}
                                                    <span class="text-gray-400 text-xs">(ID: {{ $commissariat->id }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Выбор отдела -->
                                <div class="mb-6" id="department_block" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Отдел
                                    </label>
                                    <div class="relative" style="z-index: 20;">
                                        <input type="text" id="department_search"
                                            placeholder="Введите название отдела (необязательно)"
                                            class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-gray-50"
                                            autocomplete="off" disabled>
                                        <input type="hidden" name="department_id" id="department_id">
                                        <div id="department_list"
                                            class="absolute z-50 mt-1 w-full md:w-80 bg-white border border-gray-300 rounded-lg shadow-lg max-h-72 overflow-auto hidden">
                                            <div class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500 border-b"
                                                data-id="" data-name="" data-static="true">
                                                ✕ Очистить
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Оставьте пустым для выбора самостоятельного
                                        отделения</p>
                                </div>

                                <!-- Выбор отделения -->
                                <div class="mb-6" id="division_block" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Отделение *
                                    </label>
                                    <div class="relative" style="z-index: 10;">
                                        <input type="text" id="division_search" placeholder="Введите название отделения"
                                            class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-gray-50"
                                            autocomplete="off" disabled>
                                        <input type="hidden" name="division_id" id="division_id">
                                        <div id="division_list"
                                            class="absolute z-50 mt-1 w-full md:w-80 bg-white border border-gray-300 rounded-lg shadow-lg max-h-72 overflow-auto hidden">
                                            <div class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500 border-b"
                                                data-id="" data-name="" data-static="true">
                                                ✕ Очистить
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Кнопка экспорта -->
                                <div class="flex justify-end pt-4 border-t border-gray-200 mt-6">
                                    <button type="submit" id="exportBtn" disabled
                                        class="px-6 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed transition flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span>Экспортировать</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ========== ЭЛЕМЕНТЫ ==========
            const levelSelect = document.getElementById('level_select');

            // Комиссариат
            const commissariatInput = document.getElementById('commissariat_search');
            const commissariatHidden = document.getElementById('commissariat_id');
            const commissariatList = document.getElementById('commissariat_list');

            // Отдел
            const departmentInput = document.getElementById('department_search');
            const departmentHidden = document.getElementById('department_id');
            const departmentList = document.getElementById('department_list');

            // Отделение
            const divisionInput = document.getElementById('division_search');
            const divisionHidden = document.getElementById('division_id');
            const divisionList = document.getElementById('division_list');

            // Блоки
            const commissariatBlock = document.getElementById('commissariat_block');
            const departmentBlock = document.getElementById('department_block');
            const divisionBlock = document.getElementById('division_block');

            // Кнопка экспорта
            const exportBtn = document.getElementById('exportBtn');

            // Данные для отдела и отделения
            let departmentsCache = {};
            let divisionsCache = {};

            // Загружаем данные с сервера
            @foreach ($commissariats as $commissariat)
                departmentsCache[{{ $commissariat->id }}] = @json($commissariat->departments);
                divisionsCache[{{ $commissariat->id }}] = @json($commissariat->divisions);
            @endforeach

                // ========== ФУНКЦИИ ==========

                function updateBlocksVisibility() {
                    const level = levelSelect.value;
                    commissariatBlock.style.display = 'none';
                    departmentBlock.style.display = 'none';
                    divisionBlock.style.display = 'none';

                    if (level === 'commissariat') {
                        commissariatBlock.style.display = 'block';
                    } else if (level === 'department') {
                        commissariatBlock.style.display = 'block';
                        departmentBlock.style.display = 'block';
                    } else if (level === 'division') {
                        commissariatBlock.style.display = 'block';
                        departmentBlock.style.display = 'block';
                        divisionBlock.style.display = 'block';
                    }
                }

            function checkExportButton() {
                const level = levelSelect.value;
                let isValid = false;

                if (level === 'commissariat') {
                    isValid = commissariatHidden && commissariatHidden.value !== '';
                } else if (level === 'department') {
                    isValid = commissariatHidden && commissariatHidden.value !== '';
                } else if (level === 'division') {
                    isValid = commissariatHidden && commissariatHidden.value !== '' && divisionHidden && divisionHidden.value !== '';
                }

                if (exportBtn) {
                    if (isValid) {
                        exportBtn.disabled = false;
                        exportBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                        exportBtn.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700', 'cursor-pointer');
                    } else {
                        exportBtn.disabled = true;
                        exportBtn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700', 'cursor-pointer');
                        exportBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
                    }
                }
            }

            // ========== КОМИССАРИАТ ==========
            function filterCommissariatList() {
                const query = commissariatInput.value.toLowerCase().trim();
                const items = commissariatList.querySelectorAll('div:not([data-static="true"])');
                let hasVisible = false;

                items.forEach(item => {
                    const name = (item.dataset.name || '').toLowerCase();
                    if (!query || name.includes(query)) {
                        item.classList.remove('hidden');
                        hasVisible = true;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                const staticItem = commissariatList.querySelector('[data-static="true"]');
                if (staticItem) staticItem.classList.remove('hidden');

                commissariatList.classList.toggle('hidden', !hasVisible && query !== '');
            }

            function selectCommissariat(item) {
                if (item.dataset.static === 'true') {
                    // Очистка комиссариата
                    commissariatInput.value = '';
                    commissariatHidden.value = '';

                    // Очищаем отдел и отделение
                    departmentInput.value = '';
                    departmentHidden.value = '';
                    divisionInput.value = '';
                    divisionHidden.value = '';
                    departmentInput.disabled = true;
                    divisionInput.disabled = true;
                    departmentInput.classList.add('bg-gray-50');
                    divisionInput.classList.add('bg-gray-50');

                    // Очищаем списки
                    const deptItems = departmentList.querySelectorAll('div:not([data-static="true"])');
                    deptItems.forEach(el => el.remove());
                    const divItems = divisionList.querySelectorAll('div:not([data-static="true"])');
                    divItems.forEach(el => el.remove());
                } else {
                    // Выбор комиссариата
                    commissariatInput.value = item.dataset.name || '';
                    commissariatHidden.value = item.dataset.id || '';

                    // Обновляем список отделов
                    updateDepartmentList();
                    updateDivisionList();

                    // Активируем поля
                    if (levelSelect.value === 'department' || levelSelect.value === 'division') {
                        departmentInput.disabled = false;
                        departmentInput.classList.remove('bg-gray-50');
                        departmentInput.value = '';
                        departmentInput.focus();
                    }
                    if (levelSelect.value === 'division') {
                        divisionInput.disabled = false;
                        divisionInput.classList.remove('bg-gray-50');
                        divisionInput.value = '';
                    }
                }
                commissariatList.classList.add('hidden');
                checkExportButton();
            }

            function updateDepartmentList() {
                const commissariatId = commissariatHidden.value;
                const departments = departmentsCache[commissariatId] || [];

                // Удаляем старые элементы (кроме "Очистить")
                const oldItems = departmentList.querySelectorAll('div:not([data-static="true"])');
                oldItems.forEach(item => item.remove());

                if (departments.length > 0) {
                    departments.forEach(dept => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100';
                        div.dataset.id = dept.id;
                        div.dataset.name = dept.name;
                        div.innerHTML = `${dept.name} <span class="text-gray-400 text-xs">(ID: ${dept.id})</span>`;
                        departmentList.appendChild(div);
                    });
                } else {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 text-gray-500 text-center';
                    div.textContent = '📭 Нет отделов в этом комиссариате';
                    departmentList.appendChild(div);
                }
            }

            function updateDivisionList() {
                const commissariatId = commissariatHidden.value;
                const divisions = divisionsCache[commissariatId] || [];
                const selectedDepartmentId = departmentHidden.value;

                let filteredDivisions = [];
                if (selectedDepartmentId) {
                    filteredDivisions = divisions.filter(div => div.department_id == selectedDepartmentId);
                } else {
                    filteredDivisions = divisions.filter(div => !div.department_id);
                }

                // Удаляем старые элементы (кроме "Очистить")
                const oldItems = divisionList.querySelectorAll('div:not([data-static="true"])');
                oldItems.forEach(item => item.remove());

                if (filteredDivisions.length > 0) {
                    filteredDivisions.forEach(div => {
                        const divElement = document.createElement('div');
                        divElement.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100';
                        divElement.dataset.id = div.id;
                        divElement.dataset.name = div.name;
                        divElement.dataset.departmentId = div.department_id || '';
                        divElement.innerHTML = `${div.name} <span class="text-gray-400 text-xs">(ID: ${div.id})</span>${!div.department_id ? ' <span class="text-green-500 text-xs">самостоятельное</span>' : ''}`;
                        divisionList.appendChild(divElement);
                    });
                } else {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 text-gray-500 text-center';
                    div.textContent = selectedDepartmentId ? '📭 Нет отделений в этом отделе' : '📭 Нет самостоятельных отделений';
                    divisionList.appendChild(div);
                }
            }

            // ========== ОТДЕЛ ==========
            function filterDepartmentList() {
                const query = departmentInput.value.toLowerCase().trim();
                const items = departmentList.querySelectorAll('div:not([data-static="true"])');
                let hasVisible = false;

                items.forEach(item => {
                    const name = (item.dataset.name || '').toLowerCase();
                    if (!query || name.includes(query)) {
                        item.classList.remove('hidden');
                        hasVisible = true;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                const staticItem = departmentList.querySelector('[data-static="true"]');
                if (staticItem) staticItem.classList.remove('hidden');
                departmentList.classList.toggle('hidden', !hasVisible && query !== '');
            }

            function selectDepartment(item) {
                if (item.dataset.static === 'true') {
                    // Очистка отдела
                    departmentInput.value = '';
                    departmentHidden.value = '';
                    divisionInput.value = '';
                    divisionHidden.value = '';
                    updateDivisionList();
                } else {
                    // Выбор отдела
                    departmentInput.value = item.dataset.name || '';
                    departmentHidden.value = item.dataset.id || '';
                    updateDivisionList();
                    if (levelSelect.value === 'division') {
                        divisionInput.disabled = false;
                        divisionInput.classList.remove('bg-gray-50');
                        divisionInput.value = '';
                        divisionInput.focus();
                    }
                }
                departmentList.classList.add('hidden');
                checkExportButton();
            }

            // ========== ОТДЕЛЕНИЕ ==========
            function filterDivisionList() {
                const query = divisionInput.value.toLowerCase().trim();
                const items = divisionList.querySelectorAll('div:not([data-static="true"])');
                let hasVisible = false;

                items.forEach(item => {
                    const name = (item.dataset.name || '').toLowerCase();
                    if (!query || name.includes(query)) {
                        item.classList.remove('hidden');
                        hasVisible = true;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                const staticItem = divisionList.querySelector('[data-static="true"]');
                if (staticItem) staticItem.classList.remove('hidden');
                divisionList.classList.toggle('hidden', !hasVisible && query !== '');
            }

            function selectDivision(item) {
                if (item.dataset.static === 'true') {
                    // Очистка отделения
                    divisionInput.value = '';
                    divisionHidden.value = '';
                } else {
                    // Выбор отделения
                    divisionInput.value = item.dataset.name || '';
                    divisionHidden.value = item.dataset.id || '';

                    // Если у отделения есть отдел - заполняем его
                    const departmentId = item.dataset.departmentId;
                    if (departmentId && departmentInput && departmentHidden) {
                        const commissariatId = commissariatHidden.value;
                        const departments = departmentsCache[commissariatId] || [];
                        const department = departments.find(d => d.id == departmentId);
                        if (department) {
                            departmentInput.value = department.name;
                            departmentHidden.value = departmentId;
                            departmentInput.disabled = false;
                            departmentInput.classList.remove('bg-gray-50');
                        }
                    }
                }
                divisionList.classList.add('hidden');
                checkExportButton();
            }

            // ========== ОБРАБОТЧИКИ СОБЫТИЙ ==========
            levelSelect.addEventListener('change', () => {
                updateBlocksVisibility();
                checkExportButton();
            });

            // Комиссариат
            commissariatInput.addEventListener('focus', () => {
                filterCommissariatList();
                commissariatList.classList.remove('hidden');
            });
            commissariatInput.addEventListener('input', () => {
                commissariatHidden.value = '';
                filterCommissariatList();
                checkExportButton();
            });

            // Отдел
            departmentInput.addEventListener('focus', () => {
                if (!departmentInput.disabled) {
                    filterDepartmentList();
                    departmentList.classList.remove('hidden');
                }
            });
            departmentInput.addEventListener('input', () => {
                departmentHidden.value = '';
                filterDepartmentList();
                checkExportButton();
            });

            // Отделение
            divisionInput.addEventListener('focus', () => {
                if (!divisionInput.disabled) {
                    filterDivisionList();
                    divisionList.classList.remove('hidden');
                }
            });
            divisionInput.addEventListener('input', () => {
                divisionHidden.value = '';
                filterDivisionList();
                checkExportButton();
            });

            // Клики по элементам списка комиссариатов
            const commissariatItems = commissariatList.querySelectorAll('div');
            commissariatItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectCommissariat(item);
                });
            });

            // Клики по элементам списка отделов (добавляем динамически через делегирование)
            departmentList.addEventListener('click', (e) => {
                const target = e.target.closest('div');
                if (target && target.dataset && target.dataset.id !== undefined) {
                    e.stopPropagation();
                    selectDepartment(target);
                }
            });

            // Клики по элементам списка отделений (добавляем динамически через делегирование)
            divisionList.addEventListener('click', (e) => {
                const target = e.target.closest('div');
                if (target && target.dataset && target.dataset.id !== undefined) {
                    e.stopPropagation();
                    selectDivision(target);
                }
            });

            // Закрытие списков при клике вне
            document.addEventListener('click', (e) => {
                if (!commissariatInput.contains(e.target)) commissariatList.classList.add('hidden');
                if (!departmentInput.contains(e.target)) departmentList.classList.add('hidden');
                if (!divisionInput.contains(e.target)) divisionList.classList.add('hidden');
            });

            // Отправка формы
            const form = document.getElementById('structureForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    const level = levelSelect.value;
                    let isValid = false;

                    if (level === 'commissariat') {
                        isValid = commissariatHidden.value !== '';
                    } else if (level === 'department') {
                        isValid = commissariatHidden.value !== '';
                    } else if (level === 'division') {
                        isValid = commissariatHidden.value !== '' && divisionHidden.value !== '';
                    }

                    if (!isValid) {
                        e.preventDefault();
                        alert('Пожалуйста, заполните все необходимые поля');
                    }
                });
            }

            // Инициализация
            updateBlocksVisibility();
            checkExportButton();
        });
    </script>
@endsection