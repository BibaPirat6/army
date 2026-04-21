@extends('layouts.main')

@section('header-title')
    Добавление штатной должности
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('commissariat-positions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Добавление штатной должности</h1>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('commissariat-positions.store', ['commissariat_id' => $commissariat->id]) }}"
                    method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    {{-- Отдел --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Отдел
                        </label>

                        <input type="text" id="department_search" placeholder="Выберите отдел"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                                                              focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                                                              outline-none transition-colors text-[#060606]" autocomplete="off">

                        <input type="hidden" name="department_id" id="department_id">

                        <ul id="department_list"
                            class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF] rounded-lg max-h-72 overflow-auto hidden shadow-lg">

                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id="" data-name=""
                                data-static="true">
                                ✖ Не выбирать
                            </li>

                            @foreach ($departments as $department)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $department->id }}"
                                    data-name="{{ $department->name }}">
                                    {{ $department->name }}
                                    <span class="text-gray-400 text-sm">(ID: {{ $department->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Отделение --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Отделение
                        </label>

                        <input type="text" id="division_search" placeholder="Выберите отделение"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                                                              focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                                                              outline-none transition-colors text-[#060606]" autocomplete="off" value="{{ old('division_name', '') }}">

                        <input type="hidden" name="division_id" id="division_id" value="{{ old('division_id', '') }}">

                        <ul id="division_list"
                            class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF] rounded-lg max-h-72 overflow-auto hidden shadow-lg">

                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id="" data-name=""
                                data-static="true">
                                ✖ Не выбирать
                            </li>

                            @foreach ($divisions as $division)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $division->id }}"
                                    data-name="{{ $division->name }}" data-department-id="{{ $division->department_id }}"
                                    data-department-name="{{ $division->department?->name }}"
                                    data-commissariat-id="{{ $division->commissariat->id }}"
                                    data-commissariat-name="{{ $division->commissariat->name }}">
                                    {{ $division->name }}
                                    @if ($division->department_id === null)
                                        <span class="text-green-600 text-sm">(Самостоятельное)</span>
                                    @else
                                        <span class="text-gray-400 text-sm">→ {{ $division->department?->name }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Должность --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Должность *
                        </label>

                        {{-- Видимое поле --}}
                        <input required type="text" id="position_search" placeholder="Выберите должность" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                                              focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                                              outline-none transition-colors text-[#060606]"
                            autocomplete="off">

                        {{-- Скрытое поле --}}
                        <input type="hidden" name="position_id" id="position_id">

                        {{-- Dropdown --}}
                        <ul id="position_list" class="relative z-20 mt-1 w-full bg-white border border-[#BFBFBF]
                                                           rounded-lg max-h-72 overflow-auto hidden">
                            {{-- Кнопка очистить --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id="" data-name=""
                                data-static="true">
                                Очистить
                            </li>

                            {{-- Список должностей (кроме начальников) --}}
                            @foreach ($positions as $pos)
                 
                                    <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $pos->id }}"
                                        data-name="{{ $pos->name }}">
                                        {{ $pos->name }}
                                    </li>
                        
                            @endforeach
                        </ul>
                    </div>

                    {{-- общас ставка --}}
                    <div>
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Общая ставка *
                        </label>
                        <input required type="number" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                                          focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                                          outline-none transition-colors text-[#060606]" autocomplete="off"
                            placeholder="Введите общую ставку" value="1.00" min="0.25" max="2.00" step="0.25"
                            name="rate_total">
                    </div>

                    <!-- самостоятельный -->
                    <div>
                        <label for="is_independent" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Самостоятельная должность *
                        </label>
                        <select name="is_independent" id="is_independent"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            <option value="0" selected>Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>

                    {{-- сотрудник --}}
                    {{-- начальник --}}
                    <div class="relative" id="chief-select">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Сотрудник *
                        </label>

                        {{-- visible --}}
                        <input type="text" id="chief_employee_search" placeholder="Начните вводить ФИО" autocomplete="off"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none" >

                        {{-- hidden --}}
                        <input type="hidden" name="chief_employee_id" id="chief_employee_id" >

                        {{-- dropdown --}}
                        <ul id="chief_employee_list" class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF]
                               rounded-lg max-h-72 overflow-auto hidden shadow-lg">

                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать">
                                Очистить
                            </li>

                            @foreach ($employees as $employee)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $employee->id }}"
                                    data-name="{{ trim($employee->getFullNameAttribute()) }}">
                                    {{ $employee->getFullNameAttribute() }}
                                    <span class="text-gray-400">(ID: {{ $employee->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- ставка --}}
                    <div id="rate-field"> {{-- 👈 Добавили id --}}
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            ставка *
                        </label>
                        <input type="number" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                      focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                      outline-none transition-colors text-[#060606]" autocomplete="off"
                            placeholder="Введите ставку" value="1.00" min="0.25" max="2.00" step="0.25" name="rate">
                    </div>

                    <!-- Кнопка отправки -->
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Создать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // ========== ЭЛЕМЕНТЫ ==========
        const departmentInput = document.getElementById('department_search');
        const departmentHidden = document.getElementById('department_id');
        const departmentList = document.getElementById('department_list');

        const divisionInput = document.getElementById('division_search');
        const divisionHidden = document.getElementById('division_id');
        const divisionList = document.getElementById('division_list');

        // ========== ОТДЕЛ ==========
        if (departmentInput && departmentHidden && departmentList) {

            // Фильтрация списка отделов
            function filterDepartmentList() {
                const query = departmentInput.value.toLowerCase().trim();
                const items = departmentList.querySelectorAll('li:not([data-static="true"])');
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

            // Выбор отдела
            function selectDepartment(item) {
                if (item.dataset.static === 'true') {
                    // Очистка выбора отдела
                    departmentInput.value = '';
                    departmentHidden.value = '';
                } else {
                    // Выбор отдела
                    departmentInput.value = item.dataset.name || '';
                    departmentHidden.value = item.dataset.id || '';
                }

                departmentList.classList.add('hidden');

                // Очищаем отделение при смене отдела
                if (divisionInput && divisionHidden) {
                    divisionInput.value = '';
                    divisionHidden.value = '';
                }

                // Переключаем фокус на отделение
                if (divisionInput) {
                    setTimeout(() => divisionInput.focus(), 100);
                }
            }

            // Обработчики событий для отдела
            departmentInput.addEventListener('focus', () => {
                filterDepartmentList();
                departmentList.classList.remove('hidden');
            });

            departmentInput.addEventListener('input', () => {
                departmentHidden.value = '';
                filterDepartmentList();
            });

            departmentList.querySelectorAll('li').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectDepartment(item);
                });
            });

            // Enter для выбора первого отдела
            departmentInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !departmentList.classList.contains('hidden')) {
                    e.preventDefault();
                    const firstVisible = departmentList.querySelector('li:not(.hidden):not([data-static="true"])');
                    if (firstVisible) firstVisible.click();
                }
            });
        }

        // ========== ОТДЕЛЕНИЕ ==========
        if (divisionInput && divisionHidden && divisionList) {

            // Получаем текущий выбранный отдел
            function getCurrentDepartmentId() {
                return departmentHidden ? departmentHidden.value : '';
            }

            // Фильтрация списка отделений
            function filterDivisionList() {
                const query = divisionInput.value.toLowerCase().trim();
                const currentDepartmentId = getCurrentDepartmentId();
                const items = divisionList.querySelectorAll('li:not([data-static="true"])');
                let hasVisible = false;

                items.forEach(item => {
                    const name = (item.dataset.name || '').toLowerCase();
                    const departmentId = item.dataset.departmentId || '';

                    const matchesQuery = !query || name.includes(query);

                    // ✅ Если выбран отдел - показываем только отделения этого отдела
                    // ✅ Если отдел не выбран - показываем все отделения (включая самостоятельные)
                    let matchesDepartment = true;
                    if (currentDepartmentId) {
                        matchesDepartment = departmentId === currentDepartmentId;
                    }

                    if (matchesQuery && matchesDepartment) {
                        item.classList.remove('hidden');
                        hasVisible = true;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                const staticItem = divisionList.querySelector('[data-static="true"]');
                if (staticItem) staticItem.classList.remove('hidden');

                divisionList.classList.toggle('hidden', !hasVisible && query !== '');

                // Показываем сообщение если нет отделений
                let noItemsMsg = divisionList.querySelector('.no-items-message');
                if (!noItemsMsg) {
                    noItemsMsg = document.createElement('li');
                    noItemsMsg.className = 'px-4 py-2 text-gray-500 text-center hidden no-items-message';
                    noItemsMsg.textContent = currentDepartmentId ? '📭 Нет отделений в выбранном отделе' : '📭 Нет доступных отделений';
                    divisionList.appendChild(noItemsMsg);
                }

                // Обновляем текст сообщения
                noItemsMsg.textContent = currentDepartmentId ? '📭 Нет отделений в выбранном отделе' : '📭 Нет доступных отделений';

                if (!hasVisible) {
                    noItemsMsg.classList.remove('hidden');
                } else {
                    noItemsMsg.classList.add('hidden');
                }
            }

            // Выбор отделения
            function selectDivision(item) {
                if (item.dataset.static === 'true') {
                    // Очистка выбора отделения
                    divisionInput.value = '';
                    divisionHidden.value = '';
                } else {
                    // Выбор отделения
                    divisionInput.value = item.dataset.name || '';
                    divisionHidden.value = item.dataset.id || '';

                    // ✅ Если отделение НЕ самостоятельное (имеет department_id) - заполняем отдел
                    const departmentId = item.dataset.departmentId;
                    const departmentName = item.dataset.departmentName;

                    if (departmentId && departmentName && departmentInput && departmentHidden) {
                        departmentInput.value = departmentName;
                        departmentHidden.value = departmentId;
                    }

                    // ✅ Если отделение самостоятельное - очищаем отдел
                    if (!departmentId && departmentInput && departmentHidden) {
                        departmentInput.value = '';
                        departmentHidden.value = '';
                    }
                }

                divisionList.classList.add('hidden');

                // Переключаем фокус на поле должности
                const positionSelect = document.getElementById('position_id');
                if (positionSelect) {
                    setTimeout(() => positionSelect.focus(), 100);
                }
            }

            // Обновить фильтрацию при изменении отдела
            function updateDivisionFilter() {
                if (divisionInput.value) {
                    // Если есть выбранное отделение, проверяем соответствует ли оно новому отделу
                    const selectedDivisionId = divisionHidden.value;
                    if (selectedDivisionId) {
                        const selectedItem = divisionList.querySelector(`li[data-id="${selectedDivisionId}"]`);
                        const departmentId = selectedItem?.dataset.departmentId || '';
                        const currentDepartmentId = getCurrentDepartmentId();

                        // Если выбранное отделение не принадлежит текущему отделу - очищаем
                        if (currentDepartmentId && departmentId !== currentDepartmentId) {
                            divisionInput.value = '';
                            divisionHidden.value = '';
                        }
                    }
                }
                filterDivisionList();
            }

            // Обработчики событий для отделения
            divisionInput.addEventListener('focus', () => {
                filterDivisionList();
                divisionList.classList.remove('hidden');
            });

            divisionInput.addEventListener('input', () => {
                divisionHidden.value = '';
                filterDivisionList();
            });

            divisionList.querySelectorAll('li').forEach(item => {
                if (!item.classList.contains('no-items-message')) {
                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        selectDivision(item);
                    });
                }
            });

            // Enter для выбора первого отделения
            divisionInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !divisionList.classList.contains('hidden')) {
                    e.preventDefault();
                    const firstVisible = divisionList.querySelector('li:not(.hidden):not([data-static="true"]):not(.no-items-message)');
                    if (firstVisible) firstVisible.click();
                }
            });

            // Следим за изменением отдела
            if (departmentHidden) {
                const observer = new MutationObserver(() => {
                    updateDivisionFilter();
                });
                observer.observe(departmentHidden, { attributes: true, attributeFilter: ['value'] });
            }
        }

        // ========== НАВИГАЦИЯ С КЛАВИАТУРЫ ==========
        function setupKeyboardNavigation(input, list) {
            if (!input || !list) return;

            let currentHighlight = -1;

            input.addEventListener('keydown', (e) => {
                const items = Array.from(list.querySelectorAll('li:not(.hidden):not([data-static="true"]):not(.no-items-message)'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('hidden') && items.length > 0) {
                        currentHighlight++;
                        if (currentHighlight >= items.length) currentHighlight = 0;
                        highlightItem(items, currentHighlight);
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
                        item.classList.add('bg-gray-100');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('bg-gray-100');
                    }
                });
            }

            input.addEventListener('blur', () => {
                currentHighlight = -1;
                list.querySelectorAll('li').forEach(item => {
                    item.classList.remove('bg-gray-100');
                });
            });
        }

        // Активируем навигацию
        setupKeyboardNavigation(departmentInput, departmentList);
        setupKeyboardNavigation(divisionInput, divisionList);

        // ========== ЗАКРЫТИЕ СПИСКОВ ==========
        document.addEventListener('click', (e) => {
            if (departmentList && !e.target.closest('#department_search, #department_list')?.parentElement) {
                departmentList.classList.add('hidden');
            }
            if (divisionList && !e.target.closest('#division_search, #division_list')?.parentElement) {
                divisionList.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (departmentList) departmentList.classList.add('hidden');
                if (divisionList) divisionList.classList.add('hidden');
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('position_search');
        const hiddenInput = document.getElementById('position_id');
        const list = document.getElementById('position_list');
        const items = list.querySelectorAll('li');

        // Показать список
        function openList() {
            list.classList.remove('hidden');
        }

        // Скрыть список
        function closeList() {
            list.classList.add('hidden');
        }

        // Фильтр списка по вводу
        function filterList(value) {
            const query = value.toLowerCase().trim();
            let hasVisible = false;

            items.forEach(item => {
                if (item.dataset.static === 'true') {
                    item.classList.remove('hidden');
                    hasVisible = true;
                    return;
                }
                const name = item.dataset.name.toLowerCase();
                if (!query || name.includes(query)) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            list.classList.toggle('hidden', !hasVisible);
        }

        // Клик по input → показать список
        input.addEventListener('click', () => {
            input.removeAttribute('readonly');
            filterList(input.value);
            openList();
        });

        // Ввод текста → фильтр
        input.addEventListener('input', () => {
            hiddenInput.value = '';
            filterList(input.value);
        });

        // Клик по элементам списка
        items.forEach(item => {
            item.addEventListener('click', () => {
                if (item.dataset.static === 'true') {
                    // Очистка
                    input.value = '';
                    hiddenInput.value = '';
                    input.focus();
                    filterList('');
                    return;
                }

                // Выбор должности
                input.value = item.dataset.name;
                hiddenInput.value = item.dataset.id;
                closeList();
                input.setAttribute('readonly', true);
            });
        });

        // Закрытие списка при клике вне блока
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                closeList();
                if (!hiddenInput.value) input.value = '';
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('chief-select');
        const input = container.querySelector('#chief_employee_search');
        const hidden = container.querySelector('#chief_employee_id');
        const list = container.querySelector('#chief_employee_list');
        const items = list.querySelectorAll('li');
        const rateField = document.getElementById('rate-field'); // Поле ставки

        // Функция для показа/скрытия поля ставки
        function toggleRateField() {
            if (hidden.value && hidden.value !== '') {
                // Если сотрудник выбран - показываем поле ставки
                rateField.style.display = 'block';
                // Делаем поле обязательным
                rateField.querySelector('input').required = true;
            } else {
                // Если сотрудник не выбран - скрываем поле ставки
                rateField.style.display = 'none';
                // Убираем обязательность
                rateField.querySelector('input').required = false;
                // Очищаем значение
                rateField.querySelector('input').value = '';
            }
        }

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
                const id = item.dataset.id || '';

                if (!q || name.includes(q) || id.includes(q)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // focus
        input.addEventListener('focus', () => {
            open();
            filter(input.value);
        });

        // typing
        input.addEventListener('input', () => {
            hidden.value = ''; // сбрасываем только при ручном вводе
            open();
            filter(input.value);
            toggleRateField(); // Скрываем поле при очистке
        });

        // select
        items.forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.id || '';
                const name = item.dataset.name || '';

                input.value = name;
                hidden.value = id;

                close();
                toggleRateField(); // Показываем или скрываем поле
            });
        });

        // click outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                close();
            }
        });

        // Инициализация при загрузке страницы
        toggleRateField();
    });
</script>

