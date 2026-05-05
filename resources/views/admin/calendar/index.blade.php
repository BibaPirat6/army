@extends('layouts.main')

@section('header-title')
    Календарь
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Календарный график задач</h2>
            <button type="button" onclick="openStatsModal()"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Статистика по подразделениям
            </button>
        </div>
        <div id="calendar"></div>
    </div>

    {{-- Модальное окно задачи --}}
    <div id="taskModal" class="fixed inset-0 z-[999] hidden overflow-y-auto" aria-hidden="true">
        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" data-modal-overlay></div>
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
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">Ответственный *</label>
                        <input type="text" id="employee_position_search" placeholder="Начните вводить ФИО или должность"
                            autocomplete="off"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none">
                        <input type="hidden" name="employee_position_id" id="employee_position_id">
                        <ul id="employee_position_list"
                            class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF] rounded-lg max-h-72 overflow-auto hidden shadow-lg">
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать">Очистить</li>
                            @foreach($employeePositions as $ep)
                                @php
                                    $cp = $ep->commissariatPosition;
                                    $unitName = $cp->division?->name ?? $cp->department?->name ?? $cp->commissariat?->name ?? 'Не указано';
                                    $person = $ep->employee?->person;
                                    $fullName = $person ? trim($person->фамилия . ' ' . $person->имя . ' ' . ($person->отчество ?? '')) : 'Сотрудник #' . $ep->employee_id;
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
                            <div class="dz-message text-gray-500 text-sm text-center">Перетащите файлы сюда или кликните для
                                выбора</div>
                        </div>
                    </div>
                    {{-- Кнопки --}}
                    <div class="flex justify-end gap-3">
                        <button type="button" data-modal-close
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Отмена</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Модальное окно статистики --}}
    <div id="statsModal" class="fixed inset-0 z-[999] hidden overflow-y-auto" aria-hidden="true">
        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeStatsModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 z-10">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-800">Статистика задач по подразделениям</h3>
                    <button type="button" onclick="closeStatsModal()"
                        class="text-gray-400 hover:text-gray-600 transition p-1 rounded-full hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                {{-- Уровень 1: Комиссариаты --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Комиссариат</label>
                    <div class="relative" id="statsCommissariatBlock">
                        <input type="text" id="statsCommissariatSearch" placeholder="Поиск по комиссариатам..."
                            autocomplete="off"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <ul id="statsCommissariatList"
                            class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto hidden">
                        </ul>
                    </div>
                    <div id="selectedCommissariat" class="mt-2 hidden">
                        <span
                            class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-sm">
                            <span id="selectedCommissariatName"></span>
                            <button type="button" onclick="clearStatsLevel('commissariat')"
                                class="text-indigo-400 hover:text-indigo-600">&times;</button>
                        </span>
                    </div>
                </div>
                {{-- Уровень 2: Отделы --}}
                <div class="mb-4" id="statsDepartmentBlock" style="display:none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Отдел</label>
                    <div class="relative" id="statsDepartmentSearchBlock">
                        <input type="text" id="statsDepartmentSearch" placeholder="Поиск по отделам..." autocomplete="off"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <ul id="statsDepartmentList"
                            class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto hidden">
                        </ul>
                    </div>
                    <div id="selectedDepartment" class="mt-2 hidden">
                        <span
                            class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-sm">
                            <span id="selectedDepartmentName"></span>
                            <button type="button" onclick="clearStatsLevel('department')"
                                class="text-indigo-400 hover:text-indigo-600">&times;</button>
                        </span>
                    </div>
                </div>
                {{-- Уровень 3: Отделения --}}
                <div class="mb-4" id="statsDivisionBlock" style="display:none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Отделение</label>
                    <div class="relative" id="statsDivisionSearchBlock">
                        <input type="text" id="statsDivisionSearch" placeholder="Поиск по отделениям..." autocomplete="off"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <ul id="statsDivisionList"
                            class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto hidden">
                        </ul>
                    </div>
                    <div id="selectedDivision" class="mt-2 hidden">
                        <span
                            class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-sm">
                            <span id="selectedDivisionName"></span>
                            <button type="button" onclick="clearStatsLevel('division')"
                                class="text-indigo-400 hover:text-indigo-600">&times;</button>
                        </span>
                    </div>
                </div>
                {{-- Результат --}}
                <div id="statsResult" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                    <h4 class="font-medium text-gray-700 mb-2">Сводка</h4>
                    <div id="statsResultContent" class="space-y-2 text-sm"></div>
                </div>
                <div class="flex justify-end mt-5 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeStatsModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ===== ОБЩИЕ ФУНКЦИИ МОДАЛОК =====
        window.openModal = function () {
            document.getElementById('taskModal').classList.remove('hidden');
        };
        window.closeModal = function () {
            document.getElementById('taskModal').classList.add('hidden');
        };
        window.openStatsModal = function () {
            document.getElementById('statsModal').classList.remove('hidden');
        };
        window.closeStatsModal = function () {
            document.getElementById('statsModal').classList.add('hidden');
        };
        window.resetForm = function () {
            document.getElementById('taskForm').reset();
            document.getElementById('task_id').value = '';
            document.getElementById('modalTitle').textContent = 'Новая задача';
            const taskLinkContainer = document.getElementById('taskLinkContainer');
            if (taskLinkContainer) taskLinkContainer.classList.add('hidden');
            if (typeof window.resetResponsibleSelect === 'function') window.resetResponsibleSelect();
        };

        document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () { window.closeModal(); });
        });
        const overlay = document.querySelector('[data-modal-overlay]');
        if (overlay) overlay.addEventListener('click', function () { window.closeModal(); });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.closeModal();
                window.closeStatsModal();
            }
        });

        // ===== ВЫПАДАЮЩИЙ СПИСОК ОТВЕТСТВЕННОГО =====
        (function () {
            const container = document.getElementById('responsible-select');
            if (!container) return;
            const input = container.querySelector('#employee_position_search');
            const hidden = container.querySelector('#employee_position_id');
            const list = container.querySelector('#employee_position_list');
            const items = list.querySelectorAll('li');

            function open() { list.classList.remove('hidden'); }
            function close() { list.classList.add('hidden'); }
            function filter(value) {
                const q = value.toLowerCase().trim();
                items.forEach(item => {
                    const name = (item.dataset.name || '').toLowerCase();
                    const search = (item.dataset.search || '').toLowerCase();
                    const id = item.dataset.id || '';
                    item.style.display = (!q || name.includes(q) || search.includes(q) || id.includes(q)) ? 'block' : 'none';
                });
            }
            input.addEventListener('focus', () => { open(); filter(input.value); });
            input.addEventListener('input', () => { hidden.value = ''; open(); filter(input.value); });
            items.forEach(item => {
                item.addEventListener('click', () => {
                    input.value = item.dataset.name || '';
                    hidden.value = item.dataset.id || '';
                    close();
                });
            });
            document.addEventListener('click', (e) => { if (!container.contains(e.target)) close(); });

            window.setResponsibleSelect = function (id, name) {
                if (input && name) input.value = name;
                if (hidden && id) hidden.value = id;
            };
            window.resetResponsibleSelect = function () {
                if (input) input.value = '';
                if (hidden) hidden.value = '';
            };
        })();



        // ===== СТАТИСТИКА ПОДРАЗДЕЛЕНИЙ =====
        (function () {
            const statsData = @json($taskStats);
            let selectedCommissariatId = null;
            let selectedDepartmentId = null;

            const commissariatSearch = document.getElementById('statsCommissariatSearch');
            const commissariatList = document.getElementById('statsCommissariatList');
            const departmentSearch = document.getElementById('statsDepartmentSearch');
            const departmentList = document.getElementById('statsDepartmentList');
            const divisionSearch = document.getElementById('statsDivisionSearch');
            const divisionList = document.getElementById('statsDivisionList');
            const selectedCommissariat = document.getElementById('selectedCommissariat');
            const selectedDepartment = document.getElementById('selectedDepartment');
            const selectedDivision = document.getElementById('selectedDivision');
            const selectedCommissariatName = document.getElementById('selectedCommissariatName');
            const selectedDepartmentName = document.getElementById('selectedDepartmentName');
            const selectedDivisionName = document.getElementById('selectedDivisionName');
            const statsResult = document.getElementById('statsResult');
            const statsResultContent = document.getElementById('statsResultContent');

            let currentMatrixData = null;

            // Вспомогательная функция суммирования
            function sumDivisionsTasks(divisions) {
                return divisions.reduce((sum, d) => sum + (d.tasks || 0), 0);
            }

            // ===== КОМИССАРИАТЫ =====
            function renderCommissariatList(filter = '') {
                const q = filter.toLowerCase().trim();
                commissariatList.innerHTML = '';
                const filtered = statsData.filter(c => !q || c.name.toLowerCase().includes(q));
                if (filtered.length === 0) {
                    commissariatList.innerHTML = '<div class="px-4 py-2 text-gray-400 text-sm">Ничего не найдено</div>';
                } else {
                    filtered.forEach(c => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-2 cursor-pointer hover:bg-indigo-50 flex justify-between items-center';
                        li.innerHTML = `<span>${c.name}</span><span class="text-xs text-gray-500">Всего: ${c.total} | Напрямую: ${c.direct}</span>`;
                        li.addEventListener('click', () => selectCommissariat(c));
                        commissariatList.appendChild(li);
                    });
                }
            }

            function selectCommissariat(c) {
                commissariatSearch.value = c.name;
                selectedCommissariatId = c.id;
                selectedCommissariatName.textContent = c.name;
                selectedCommissariat.classList.remove('hidden');
                commissariatList.classList.add('hidden');
                document.getElementById('statsDepartmentBlock').style.display = 'block';
                document.getElementById('statsDivisionBlock').style.display = 'block';
                departmentSearch.value = '';
                departmentSearch.disabled = false;
                divisionSearch.value = '';
                divisionSearch.disabled = false;
                selectedDepartmentId = null;
                selectedDepartment.classList.add('hidden');
                selectedDivision.classList.add('hidden');
                renderDepartmentList();
                renderIndependentDivisionsList(c);
                showStatsResult('commissariat', c);
            }

            // ===== ОТДЕЛЫ =====
            function renderDepartmentList(filter = '') {
                const q = filter.toLowerCase().trim();
                departmentList.innerHTML = '';
                const commissariat = statsData.find(c => c.id == selectedCommissariatId);
                if (!commissariat) return;

                const indDivSum = sumDivisionsTasks(commissariat.independent_divisions || []);
                const deptSum = commissariat.total - commissariat.direct - indDivSum;

                const allLi = document.createElement('li');
                allLi.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 text-indigo-600 font-medium border-b';
                allLi.innerHTML = `📁 Все отделы (Всего: ${deptSum})`;
                allLi.addEventListener('click', () => {
                    selectedDepartmentId = null;
                    departmentSearch.value = '';
                    selectedDepartment.classList.add('hidden');
                    selectedDivision.classList.add('hidden');
                    renderIndependentDivisionsList(commissariat);
                    showStatsResult('commissariat', commissariat);
                    departmentList.classList.add('hidden');
                });
                departmentList.appendChild(allLi);

                const filtered = commissariat.departments.filter(d => !q || d.name.toLowerCase().includes(q));
                if (filtered.length === 0 && q) {
                    departmentList.innerHTML += '<div class="px-4 py-2 text-gray-400 text-sm">Ничего не найдено</div>';
                } else {
                    filtered.forEach(d => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-2 cursor-pointer hover:bg-indigo-50 flex justify-between items-center';
                        li.innerHTML = `<span>${d.name}</span><span class="text-xs text-gray-500">Всего: ${d.total} | Напрямую: ${d.direct}</span>`;
                        li.addEventListener('click', () => selectDepartment(d));
                        departmentList.appendChild(li);
                    });
                }
            }

            function selectDepartment(d) {
                departmentSearch.value = d.name;
                selectedDepartmentId = d.id;
                selectedDepartmentName.textContent = d.name;
                selectedDepartment.classList.remove('hidden');
                departmentList.classList.add('hidden');
                divisionSearch.value = '';
                divisionSearch.disabled = false;
                selectedDivision.classList.add('hidden');
                renderDivisionList(d);
                showStatsResult('department', d);
            }

            // ===== ОТДЕЛЕНИЯ ПРИВЯЗАННЫЕ К ОТДЕЛУ =====
            function renderDivisionList(department) {
                divisionList.innerHTML = '';
                const divisions = department.divisions || [];

                const allLi = document.createElement('li');
                allLi.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 text-indigo-600 font-medium border-b';
                allLi.innerHTML = `📋 Все отделения (Всего: ${department.total - department.direct})`;
                allLi.addEventListener('click', () => {
                    divisionSearch.value = '';
                    selectedDivision.classList.add('hidden');
                    currentMatrixData = { type: 'department', id: department.id, commissariatId: selectedCommissariatId, departmentId: department.id };
                    showStatsResult('department', department);
                    divisionList.classList.add('hidden');
                });
                divisionList.appendChild(allLi);

                if (divisions.length === 0) {
                    divisionList.innerHTML += '<div class="px-4 py-2 text-gray-400 text-sm">Нет отделений</div>';
                } else {
                    divisions.forEach(d => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-2 cursor-pointer hover:bg-indigo-50 flex justify-between items-center';
                        li.innerHTML = `<span>${d.name}</span><span class="text-xs text-gray-500">Задач: ${d.tasks}</span>`;
                        li.addEventListener('click', () => selectDivision(d));
                        divisionList.appendChild(li);
                    });
                }
            }

            // ===== САМОСТОЯТЕЛЬНЫЕ ОТДЕЛЕНИЯ =====
            function renderIndependentDivisionsList(commissariat) {
                const divisions = commissariat.independent_divisions || [];

                if (divisions.length === 0) {
                    divisionList.innerHTML = '<div class="px-4 py-2 text-gray-400 text-sm">Нет самостоятельных отделений</div>';
                } else {
                    divisionList.innerHTML = '';
                    const allLi = document.createElement('li');
                    allLi.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 text-indigo-600 font-medium border-b';
                    allLi.innerHTML = `📋 Все самост. отделения (Всего: ${sumDivisionsTasks(divisions)})`;
                    allLi.addEventListener('click', () => {
                        divisionSearch.value = '';
                        selectedDivision.classList.add('hidden');
                        currentMatrixData = { type: 'commissariat', id: commissariat.id, commissariatId: commissariat.id, departmentId: null };
                        showStatsResult('commissariat', commissariat);
                        divisionList.classList.add('hidden');
                    });
                    divisionList.appendChild(allLi);

                    divisions.forEach(d => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-2 cursor-pointer hover:bg-indigo-50 flex justify-between items-center';
                        li.innerHTML = `<span>${d.name}</span><span class="text-xs text-gray-500">Задач: ${d.tasks}</span>`;
                        li.addEventListener('click', () => selectIndependentDivision(d, commissariat));
                        divisionList.appendChild(li);
                    });
                }
            }

            function selectDivision(d) {
                divisionSearch.value = d.name;
                selectedDivisionName.textContent = d.name;
                selectedDivision.classList.remove('hidden');
                divisionList.classList.add('hidden');

                currentMatrixData = {
                    type: 'division',
                    id: d.id,
                    commissariatId: selectedCommissariatId,
                    departmentId: selectedDepartmentId,
                };

                showStatsResult('division', d);
            }

            function selectIndependentDivision(d, commissariat) {
                divisionSearch.value = d.name;
                selectedDivisionName.textContent = d.name;
                selectedDivision.classList.remove('hidden');
                divisionList.classList.add('hidden');

                currentMatrixData = {
                    type: 'division',
                    id: d.id,
                    commissariatId: commissariat.id,
                    departmentId: null,
                };

                showStatsResult('division', d);
            }

            // ===== ПОКАЗ РЕЗУЛЬТАТА + КНОПКА МАТРИЦЫ =====
            function showStatsResult(level, data) {
                statsResult.classList.remove('hidden');
                let html = '';
                let matrixUrl = null;
                let hasMatrix = false;

                if (level === 'commissariat') {
                    const commissariat = statsData.find(c => c.id == selectedCommissariatId);
                    const indDivSum = commissariat ? sumDivisionsTasks(commissariat.independent_divisions || []) : 0;
                    const deptTasks = data.total - data.direct - indDivSum;

                    html = `
                        <div class="flex justify-between"><span class="text-gray-500">🏛 Комиссариат:</span><span class="font-medium">${data.name}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">↳ Прямые задачи:</span><span class="font-medium">${data.direct}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">📁 Задачи отделов:</span><span class="font-medium">${deptTasks}</span></div>
                        ${indDivSum > 0 ? `<div class="flex justify-between"><span class="text-gray-500">📋 Задачи самост. отделений:</span><span class="font-medium">${indDivSum}</span></div>` : ''}
                        <div class="flex justify-between border-t pt-1 mt-1"><span class="font-semibold text-gray-700">Общее количество:</span><span class="font-bold text-indigo-700">${data.total}</span></div>
                    `;
                    hasMatrix = data.total > 0;
                    matrixUrl = `/calendar/matrix/${data.id}`;
                } else if (level === 'department') {
                    html = `
                        <div class="flex justify-between"><span class="text-gray-500">📁 Отдел:</span><span class="font-medium">${data.name}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">↳ Прямые задачи:</span><span class="font-medium">${data.direct}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">📋 Задачи отделений:</span><span class="font-medium">${data.total - data.direct}</span></div>
                        <div class="flex justify-between border-t pt-1 mt-1"><span class="font-semibold text-gray-700">Всего по отделу:</span><span class="font-bold text-indigo-700">${data.total}</span></div>
                    `;
                    hasMatrix = data.total > 0;
                    matrixUrl = `/calendar/matrix/${selectedCommissariatId}/department/${data.id}`;
                } else if (level === 'division') {
                    html = `
                        <div class="flex justify-between"><span class="text-gray-500">📋 Отделение:</span><span class="font-medium">${data.name}</span></div>
                        <div class="flex justify-between border-t pt-1 mt-1"><span class="font-semibold text-gray-700">Количество задач:</span><span class="font-bold text-indigo-700">${data.tasks}</span></div>
                    `;
                    hasMatrix = data.tasks > 0;
                    if (currentMatrixData) {
                        const deptPart = currentMatrixData.departmentId ? `department/${currentMatrixData.departmentId}/` : '';
                        matrixUrl = `/calendar/matrix/${currentMatrixData.commissariatId}/${deptPart}division/${data.id}`;
                    }
                }

                // Кнопка матрицы
                if (hasMatrix && matrixUrl) {
                    html += `
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <a href="${matrixUrl}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                Матрица сотрудников
                            </a>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <button type="button" disabled
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                Матрица сотрудников (нет задач)
                            </button>
                        </div>
                    `;
                }

                statsResultContent.innerHTML = html;
            }

            // ===== ОЧИСТКА =====
            window.clearStatsLevel = function (level) {
                if (level === 'commissariat') {
                    commissariatSearch.value = '';
                    selectedCommissariatId = null;
                    selectedCommissariat.classList.add('hidden');
                    document.getElementById('statsDepartmentBlock').style.display = 'none';
                    document.getElementById('statsDivisionBlock').style.display = 'none';
                    departmentSearch.value = ''; departmentSearch.disabled = true;
                    divisionSearch.value = ''; divisionSearch.disabled = true;
                    selectedDepartment.classList.add('hidden');
                    selectedDivision.classList.add('hidden');
                    statsResult.classList.add('hidden');
                    currentMatrixData = null;
                } else if (level === 'department') {
                    departmentSearch.value = '';
                    selectedDepartmentId = null;
                    selectedDepartment.classList.add('hidden');
                    selectedDivision.classList.add('hidden');
                    const commissariat = statsData.find(c => c.id == selectedCommissariatId);
                    if (commissariat) {
                        renderIndependentDivisionsList(commissariat);
                        showStatsResult('commissariat', commissariat);
                    }
                } else if (level === 'division') {
                    divisionSearch.value = '';
                    selectedDivision.classList.add('hidden');
                    currentMatrixData = null;
                    const commissariat = statsData.find(c => c.id == selectedCommissariatId);
                    if (selectedDepartmentId) {
                        const dept = commissariat?.departments.find(d => d.id == selectedDepartmentId);
                        if (dept) showStatsResult('department', dept);
                    } else if (commissariat) {
                        showStatsResult('commissariat', commissariat);
                    }
                }
            };

            // ===== СОБЫТИЯ =====
            commissariatSearch.addEventListener('focus', () => { commissariatList.classList.remove('hidden'); renderCommissariatList(commissariatSearch.value); });
            commissariatSearch.addEventListener('input', () => { commissariatList.classList.remove('hidden'); renderCommissariatList(commissariatSearch.value); });
            departmentSearch.addEventListener('focus', () => { if (!departmentSearch.disabled) { departmentList.classList.remove('hidden'); renderDepartmentList(departmentSearch.value); } });
            departmentSearch.addEventListener('input', () => { departmentList.classList.remove('hidden'); renderDepartmentList(departmentSearch.value); });
            divisionSearch.addEventListener('focus', () => { if (!divisionSearch.disabled) { divisionList.classList.remove('hidden'); } });
            divisionSearch.addEventListener('input', () => { divisionList.classList.remove('hidden'); });

            document.addEventListener('click', (e) => {
                if (!document.getElementById('statsCommissariatBlock').contains(e.target)) commissariatList.classList.add('hidden');
                if (!document.getElementById('statsDepartmentSearchBlock').contains(e.target)) departmentList.classList.add('hidden');
                if (!document.getElementById('statsDivisionSearchBlock').contains(e.target)) divisionList.classList.add('hidden');
            });
        })();

    </script>
@endpush