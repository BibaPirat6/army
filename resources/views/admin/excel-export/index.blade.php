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

                <!-- Структура (с выбором комиссариата) -->
                <div class="bg-white border rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="font-medium">Структура комиссариата</span>
                        </div>
                    </div>

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
                                <select id="level_select"
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
                                    <input type="text" id="commissariat_search" placeholder="Введите название комиссариата"
                                        class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        autocomplete="off">
                                    <input type="hidden" id="commissariat_id">
                                    <div id="commissariat_list"
                                        class="absolute z-50 mt-1 w-full md:w-80 bg-white border border-gray-300 rounded-lg shadow-lg max-h-72 overflow-auto hidden">
                                        <div class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500 border-b" data-id="" data-name="" data-static="true">
                                            ✕ Очистить
                                        </div>
                                        @foreach ($commissariats as $commissariat)
                                            <div class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $commissariat->id }}" data-name="{{ $commissariat->name }}">
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
                                    Отдел *
                                </label>
                                <div class="relative" style="z-index: 20;">
                                    <input type="text" id="department_search" placeholder="Введите название отдела"
                                        class="w-full md:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-gray-50"
                                        autocomplete="off" disabled>
                                    <input type="hidden" id="department_id">
                                    <div id="department_list"
                                        class="absolute z-50 mt-1 w-full md:w-80 bg-white border border-gray-300 rounded-lg shadow-lg max-h-72 overflow-auto hidden">
                                        <div class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500 border-b" data-id="" data-name="" data-static="true">
                                            ✕ Очистить
                                        </div>
                                    </div>
                                </div>
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
                                    <input type="hidden" id="division_id">
                                    <div id="division_list"
                                        class="absolute z-50 mt-1 w-full md:w-80 bg-white border border-gray-300 rounded-lg shadow-lg max-h-72 overflow-auto hidden">
                                        <div class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500 border-b" data-id="" data-name="" data-static="true">
                                            ✕ Очистить
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Кнопка экспорта -->
                            <div class="flex justify-end pt-4 border-t border-gray-200 mt-6">
                                <button type="button" id="exportBtn" disabled
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

        // Обновить видимость блоков
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

        // Проверить кнопку экспорта
        function checkExportButton() {
            const level = levelSelect.value;
            let isValid = false;

            if (level === 'commissariat') {
                isValid = commissariatHidden && commissariatHidden.value !== '';
            } else if (level === 'department') {
                isValid = commissariatHidden && commissariatHidden.value !== '' &&
                    departmentHidden && departmentHidden.value !== '';
            } else if (level === 'division') {
                isValid = commissariatHidden && commissariatHidden.value !== '' &&
                    divisionHidden && divisionHidden.value !== '';
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

        // Сброс формы
        function resetForm() {
            if (commissariatInput) commissariatInput.value = '';
            if (commissariatHidden) commissariatHidden.value = '';
            if (departmentInput) {
                departmentInput.value = '';
                departmentInput.disabled = true;
                departmentInput.classList.add('bg-gray-50');
            }
            if (departmentHidden) departmentHidden.value = '';
            if (divisionInput) {
                divisionInput.value = '';
                divisionInput.disabled = true;
                divisionInput.classList.add('bg-gray-50');
            }
            if (divisionHidden) divisionHidden.value = '';

            // Очищаем списки
            if (departmentList) {
                const oldItems = departmentList.querySelectorAll('div:not([data-static="true"])');
                oldItems.forEach(item => item.remove());
            }
            if (divisionList) {
                const oldItems = divisionList.querySelectorAll('div:not([data-static="true"])');
                oldItems.forEach(item => item.remove());
            }
        }

        // ========== КОМИССАРИАТ ==========
        if (commissariatInput && commissariatHidden && commissariatList) {

            // Фильтрация списка комиссариатов
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

            // Выбор комиссариата (ОБНОВЛЕНО: добавлена очистка отделов и отделений)
            function selectCommissariat(item) {
                if (item.dataset.static === 'true') {
                    // Очистка комиссариата
                    commissariatInput.value = '';
                    commissariatHidden.value = '';

                    // Очищаем отдел и отделение
                    if (departmentInput) {
                        departmentInput.value = '';
                        departmentInput.disabled = true;
                        departmentInput.classList.add('bg-gray-50');
                    }
                    if (departmentHidden) departmentHidden.value = '';
                    if (divisionInput) {
                        divisionInput.value = '';
                        divisionInput.disabled = true;
                        divisionInput.classList.add('bg-gray-50');
                    }
                    if (divisionHidden) divisionHidden.value = '';

                    // Очищаем списки
                    if (departmentList) {
                        const oldItems = departmentList.querySelectorAll('div:not([data-static="true"])');
                        oldItems.forEach(el => el.remove());
                    }
                    if (divisionList) {
                        const oldItems = divisionList.querySelectorAll('div:not([data-static="true"])');
                        oldItems.forEach(el => el.remove());
                    }

                } else {
                    // Выбор комиссариата
                    commissariatInput.value = item.dataset.name || '';
                    commissariatHidden.value = item.dataset.id || '';

                    // Обновляем список отделов и отделений
                    updateDepartmentList();
                    updateDivisionList();

                    // Активируем поле отдела (если уровень позволяет)
                    if (levelSelect.value === 'department' || levelSelect.value === 'division') {
                        if (departmentInput) {
                            departmentInput.disabled = false;
                            departmentInput.classList.remove('bg-gray-50');
                            departmentInput.value = '';
                            departmentInput.focus();
                        }
                    }
                    
                    // Активируем поле отделения (если уровень division)
                    if (levelSelect.value === 'division') {
                        if (divisionInput) {
                            divisionInput.disabled = false;
                            divisionInput.classList.remove('bg-gray-50');
                            divisionInput.value = '';
                        }
                    }
                }

                commissariatList.classList.add('hidden');
                checkExportButton();
            }

            // Обновить список отделов
            function updateDepartmentList() {
                const commissariatId = commissariatHidden.value;
                const departments = departmentsCache[commissariatId] || [];

                if (departmentList) {
                    // Удаляем старые элементы (кроме "Очистить")
                    const oldItems = departmentList.querySelectorAll('div:not([data-static="true"])');
                    oldItems.forEach(item => item.remove());

                    // Добавляем новые отделы
                    if (departments.length > 0) {
                        departments.forEach(dept => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100';
                            div.dataset.id = dept.id;
                            div.dataset.name = dept.name;
                            div.dataset.commissariatId = commissariatId;
                            div.innerHTML = `${dept.name} <span class="text-gray-400 text-xs">(ID: ${dept.id})</span>`;
                            departmentList.appendChild(div);

                            div.addEventListener('click', (e) => {
                                e.stopPropagation();
                                selectDepartment(div);
                            });
                        });
                    } else {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 text-gray-500 text-center';
                        div.textContent = '📭 Нет отделов в этом комиссариате';
                        div.style.cursor = 'default';
                        departmentList.appendChild(div);
                    }
                }
            }

            // Обновить список отделений
            function updateDivisionList() {
                const commissariatId = commissariatHidden.value;
                const divisions = divisionsCache[commissariatId] || [];
                const selectedDepartmentId = departmentHidden ? departmentHidden.value : null;

                if (divisionList) {
                    // Удаляем старые элементы (кроме "Очистить")
                    const oldItems = divisionList.querySelectorAll('div:not([data-static="true"])');
                    oldItems.forEach(item => item.remove());

                    // Фильтруем отделения
                    let filteredDivisions = [];
                    
                    if (selectedDepartmentId) {
                        // Если выбран отдел - показываем только отделения этого отдела
                        filteredDivisions = divisions.filter(div => div.department_id == selectedDepartmentId);
                    } else {
                        // Если отдел не выбран - показываем ВСЕ отделения
                        filteredDivisions = divisions;
                    }

                    // Разделяем на самостоятельные и несамостоятельные
                    const independentDivisions = filteredDivisions.filter(div => !div.department_id);
                    const dependentDivisions = filteredDivisions.filter(div => div.department_id);

                    // Добавляем самостоятельные отделения
                    if (independentDivisions.length > 0) {
                        const header = document.createElement('div');
                        header.className = 'px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold';
                        header.textContent = '📌 Самостоятельные отделения';
                        divisionList.appendChild(header);

                        independentDivisions.forEach(div => {
                            const divElement = document.createElement('div');
                            divElement.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 ml-4';
                            divElement.dataset.id = div.id;
                            divElement.dataset.name = div.name;
                            divElement.dataset.departmentId = div.department_id || '';
                            divElement.innerHTML = `${div.name} <span class="text-gray-400 text-xs">(ID: ${div.id})</span> <span class="text-green-500 text-xs ml-2">самостоятельное</span>`;
                            divisionList.appendChild(divElement);

                            divElement.addEventListener('click', (e) => {
                                e.stopPropagation();
                                selectDivision(divElement);
                            });
                        });
                    }

                    // Добавляем отделения в составе отделов
                    if (dependentDivisions.length > 0) {
                        const header = document.createElement('div');
                        header.className = 'px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold mt-2';
                        header.textContent = '📁 Отделения в составе отделов';
                        divisionList.appendChild(header);

                        // Группируем по отделам
                        const groupedByDepartment = {};
                        dependentDivisions.forEach(div => {
                            const deptId = div.department_id;
                            if (!groupedByDepartment[deptId]) {
                                groupedByDepartment[deptId] = [];
                            }
                            groupedByDepartment[deptId].push(div);
                        });

                        // Добавляем отделения с группировкой
                        for (const deptId in groupedByDepartment) {
                            const department = departmentsCache[commissariatId]?.find(d => d.id == deptId);
                            if (department) {
                                const deptHeader = document.createElement('div');
                                deptHeader.className = 'px-4 py-2 bg-gray-50 text-gray-700 text-sm font-medium ml-2';
                                deptHeader.textContent = `▸ ${department.name}`;
                                divisionList.appendChild(deptHeader);

                                groupedByDepartment[deptId].forEach(div => {
                                    const divElement = document.createElement('div');
                                    divElement.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 ml-8';
                                    divElement.dataset.id = div.id;
                                    divElement.dataset.name = div.name;
                                    divElement.dataset.departmentId = div.department_id || '';
                                    divElement.innerHTML = `${div.name} <span class="text-gray-400 text-xs">(ID: ${div.id})</span>`;
                                    divisionList.appendChild(divElement);

                                    divElement.addEventListener('click', (e) => {
                                        e.stopPropagation();
                                        selectDivision(divElement);
                                    });
                                });
                            }
                        }
                    }

                    if (filteredDivisions.length === 0) {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 text-gray-500 text-center';
                        div.textContent = selectedDepartmentId ? '📭 Нет отделений в этом отделе' : '📭 Нет отделений в этом комиссариате';
                        div.style.cursor = 'default';
                        divisionList.appendChild(div);
                    }
                }
            }

            // Обработчики для комиссариата
            commissariatInput.addEventListener('focus', () => {
                filterCommissariatList();
                commissariatList.classList.remove('hidden');
            });

            commissariatInput.addEventListener('input', () => {
                commissariatHidden.value = '';
                filterCommissariatList();
                checkExportButton();
            });

            // Обработчик для кнопки "Очистить" в комиссариате
            const commissariatClearBtn = commissariatList.querySelector('[data-static="true"]');
            if (commissariatClearBtn) {
                commissariatClearBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectCommissariat(commissariatClearBtn);
                });
            }

            const commissariatItems = commissariatList.querySelectorAll('div:not([data-static="true"])');
            commissariatItems.forEach(item => {
                if (!item.classList.contains('no-items-message')) {
                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        selectCommissariat(item);
                    });
                }
            });
        }

        // ========== ОТДЕЛ ==========
        if (departmentInput && departmentHidden && departmentList) {

            // Фильтрация списка отделов
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

            // Выбор отдела (ОБНОВЛЕНО: добавлена очистка отделения)
            function selectDepartment(item) {
                if (item.dataset.static === 'true') {
                    // Очистка отдела
                    departmentInput.value = '';
                    departmentHidden.value = '';

                    // Очищаем отделение
                    if (divisionInput) {
                        divisionInput.value = '';
                        divisionInput.disabled = false;
                        divisionInput.classList.remove('bg-gray-50');
                    }
                    if (divisionHidden) divisionHidden.value = '';

                    // Обновляем список отделений (показываем все, включая самостоятельные)
                    updateDivisionList();

                } else {
                    // Выбор отдела
                    departmentInput.value = item.dataset.name || '';
                    departmentHidden.value = item.dataset.id || '';

                    // Обновляем список отделений (только для этого отдела)
                    updateDivisionList();

                    // Активируем поле отделения
                    if (divisionInput) {
                        divisionInput.disabled = false;
                        divisionInput.classList.remove('bg-gray-50');
                        divisionInput.value = '';
                        divisionInput.focus();
                    }
                }

                departmentList.classList.add('hidden');
                checkExportButton();
            }

            // Обработчики для отдела
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

            // Обработчик для кнопки "Очистить" в отделе
            const departmentClearBtn = departmentList.querySelector('[data-static="true"]');
            if (departmentClearBtn) {
                departmentClearBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectDepartment(departmentClearBtn);
                });
            }

            // Enter для выбора
            departmentInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !departmentList.classList.contains('hidden')) {
                    e.preventDefault();
                    const firstVisible = departmentList.querySelector('div:not(.hidden):not([data-static="true"])');
                    if (firstVisible) firstVisible.click();
                }
            });
        }

        // ========== ОТДЕЛЕНИЕ ==========
        if (divisionInput && divisionHidden && divisionList) {

            // Фильтрация списка отделений
            function filterDivisionList() {
                const query = divisionInput.value.toLowerCase().trim();
                const items = divisionList.querySelectorAll('div:not([data-static="true"]):not(.bg-gray-100):not(.bg-gray-50)');
                let hasVisible = false;

                items.forEach(item => {
                    if (item.dataset.name) {
                        const name = (item.dataset.name || '').toLowerCase();
                        if (!query || name.includes(query)) {
                            item.classList.remove('hidden');
                            hasVisible = true;
                        } else {
                            item.classList.add('hidden');
                        }
                    }
                });

                // Скрываем заголовки групп, если в них нет видимых элементов
                const headers = divisionList.querySelectorAll('.bg-gray-100, .bg-gray-50');
                headers.forEach(header => {
                    let nextElements = [];
                    let next = header.nextElementSibling;
                    while (next && !next.classList.contains('bg-gray-100') && !next.classList.contains('bg-gray-50')) {
                        nextElements.push(next);
                        next = next.nextElementSibling;
                    }
                    
                    const hasVisibleChildren = nextElements.some(el => !el.classList.contains('hidden'));
                    if (hasVisibleChildren) {
                        header.classList.remove('hidden');
                    } else {
                        header.classList.add('hidden');
                    }
                });

                const staticItem = divisionList.querySelector('[data-static="true"]');
                if (staticItem) staticItem.classList.remove('hidden');

                divisionList.classList.toggle('hidden', !hasVisible && query !== '');
            }

            // Выбор отделения (ОБНОВЛЕНО: полная очистка)
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
                        // Находим название отдела по ID
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

            // Обработчики для отделения
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

            // Обработчик для кнопки "Очистить" в отделении
            const divisionClearBtn = divisionList.querySelector('[data-static="true"]');
            if (divisionClearBtn) {
                divisionClearBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectDivision(divisionClearBtn);
                });
            }

            // Enter для выбора
            divisionInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !divisionList.classList.contains('hidden')) {
                    e.preventDefault();
                    const firstVisible = divisionList.querySelector('div:not(.hidden):not([data-static="true"]):not(.bg-gray-100):not(.bg-gray-50)');
                    if (firstVisible) firstVisible.click();
                }
            });
        }

        // ========== НАВИГАЦИЯ С КЛАВИАТУРЫ ==========
        function setupKeyboardNavigation(input, list) {
            if (!input || !list) return;

            let currentHighlight = -1;

            input.addEventListener('keydown', (e) => {
                const items = Array.from(list.querySelectorAll('div:not(.hidden):not([data-static="true"]):not(.bg-gray-100):not(.bg-gray-50)'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('hidden') && items.length > 0) {
                        currentHighlight++;
                        if (currentHighlight >= items.length) currentHighlight = 0;
                        highlightItem(items, currentHighlight);
                    } else if (!list.classList.contains('hidden')) {
                        list.classList.remove('hidden');
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!list.classList.contains('hidden') && items.length > 0) {
                        currentHighlight--;
                        if (currentHighlight < 0) currentHighlight = items.length - 1;
                        highlightItem(items, currentHighlight);
                    }
                } else if (e.key === 'Enter' && currentHighlight >= 0) {
                    e.preventDefault();
                    if (items[currentHighlight]) {
                        items[currentHighlight].click();
                        currentHighlight = -1;
                    }
                }
            });

            function highlightItem(items, index) {
                items.forEach((item, i) => {
                    if (i === index) {
                        item.classList.add('bg-blue-50');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('bg-blue-50');
                    }
                });
            }

            input.addEventListener('blur', () => {
                currentHighlight = -1;
                if (list) {
                    list.querySelectorAll('div').forEach(item => {
                        item.classList.remove('bg-blue-50');
                    });
                }
            });
        }

        // Активируем навигацию
        setupKeyboardNavigation(commissariatInput, commissariatList);
        setupKeyboardNavigation(departmentInput, departmentList);
        setupKeyboardNavigation(divisionInput, divisionList);

        // ========== ИНИЦИАЛИЗАЦИЯ ==========

        // Слушаем изменение уровня
        if (levelSelect) {
            levelSelect.addEventListener('change', () => {
                updateBlocksVisibility();
                resetForm();
                checkExportButton();

                // Если выбран комиссариат - активируем поле
                if (levelSelect.value === 'commissariat' && commissariatInput) {
                    commissariatInput.disabled = false;
                    commissariatInput.classList.remove('bg-gray-50');
                    setTimeout(() => commissariatInput.focus(), 100);
                } else if (levelSelect.value === 'department' && commissariatInput) {
                    commissariatInput.disabled = false;
                    commissariatInput.classList.remove('bg-gray-50');
                } else if (levelSelect.value === 'division' && commissariatInput) {
                    commissariatInput.disabled = false;
                    commissariatInput.classList.remove('bg-gray-50');
                } else if (commissariatInput) {
                    commissariatInput.disabled = false;
                    commissariatInput.classList.remove('bg-gray-50');
                }
            });

            // Запускаем начальную инициализацию
            updateBlocksVisibility();
            if (commissariatInput) {
                commissariatInput.disabled = false;
                commissariatInput.classList.remove('bg-gray-50');
            }
            if (departmentInput) {
                departmentInput.disabled = true;
                departmentInput.classList.add('bg-gray-50');
            }
            if (divisionInput) {
                divisionInput.disabled = true;
                divisionInput.classList.add('bg-gray-50');
            }
        }

        // Кнопка экспорта
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                const level = levelSelect.value;
                const commissariatId = commissariatHidden ? commissariatHidden.value : null;
                const departmentId = departmentHidden ? departmentHidden.value : null;
                const divisionId = divisionHidden ? divisionHidden.value : null;

                let url = '';
                if (level === 'commissariat' && commissariatId) {
                    url = "{{ url('/excel-export/structure') }}/" + commissariatId;
                } else if (level === 'department' && departmentId) {
                    url = "{{ url('/excel-export/structure/department') }}/" + departmentId;
                } else if (level === 'division' && divisionId) {
                    url = "{{ url('/excel-export/structure/division') }}/" + divisionId;
                }

                if (url) {
                    window.location.href = url;
                }
            });
        }

        // Закрытие списков при клике вне
        document.addEventListener('click', (e) => {
            if (commissariatList && !e.target.closest('#commissariat_search, #commissariat_list')) {
                commissariatList.classList.add('hidden');
            }
            if (departmentList && !e.target.closest('#department_search, #department_list')) {
                departmentList.classList.add('hidden');
            }
            if (divisionList && !e.target.closest('#division_search, #division_list')) {
                divisionList.classList.add('hidden');
            }
        });

        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (commissariatList) commissariatList.classList.add('hidden');
                if (departmentList) departmentList.classList.add('hidden');
                if (divisionList) divisionList.classList.add('hidden');
            }
        });
    });
</script>
@endsection