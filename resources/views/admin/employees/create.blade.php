@extends('layouts.main')

@section('header-title')
    Создание сотрудника
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('employees.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Создание сотрудника</h1>
            <p class="text-[#565A5B] mt-1">Создание данных сотрудника</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('employees.store') }}" method="post" class="space-y-6"
                    enctype="multipart/form-data">
                    @csrf


                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    {{-- person --}}
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Фамилия -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Фамилия
                                </label>
                                <input type="text" name="last_name" id="last_name" placeholder="Введите фамилию"
                                    value="{{ old('last_name') }}" required
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                            </div>

                            <!-- Имя -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Имя
                                </label>
                                <input type="text" name="first_name" id="first_name" placeholder="Введите имя"
                                    value="{{ old('first_name') }}" required
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                            </div>

                            <!-- Отчество -->
                            <div class="md:col-span-2">
                                <label for="patronymic" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Отчество
                                </label>
                                <input type="text" name="patronymic" id="patronymic" placeholder="Введите отчество"
                                    value="{{ old('patronymic') }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                            </div>

                            <!-- Почта -->
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-[#565A5B]">
                                    Почты
                                </label>

                                <div id="emails-wrapper" class="space-y-2">
                                </div>

                                <button type="button" onclick="addEmail()" class="text-sm text-[#A60644] mt-2">
                                    + Добавить почту
                                </button>
                            </div>

                            <!-- Телефон -->
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-[#565A5B]">
                                    Телефоны
                                </label>

                                <div id="phones-wrapper" class="space-y-2">
                                </div>

                                <button type="button" onclick="addPhone()" class="text-sm text-[#A60644] mt-2">
                                    + Добавить телефон
                                </button>
                            </div>


                            <!-- Фото -->
                            <div class="md:col-span-2">
                                <label for="photo" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Фото
                                </label>
                                <input type="file" name="photo" id="photo" accept="image/*"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#A60644] file:text-white file:font-medium file:cursor-pointer hover:file:bg-[#A60644]/80 transition-colors text-[#060606]">
                            </div>
                        </div>
                    </div>

                    {{-- user --}}
                    <div>
                        <!-- Логин -->
                        <div>
                            <label for="login" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Логин *
                            </label>
                            <input type="text" name="login" id="login" placeholder="Введите логин"
                                value="{{ old('login') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                        </div>


                        <!-- Пароль -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Пароль *
                            </label>
                            <input type="password" name="password" id="password" required placeholder="Введите пароль"
                                value=""
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                        </div>

                        <!-- Роль -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Роль
                            </label>
                            <select name="role" id="role"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">
                                        {{ $role->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <!-- Рабочий статус -->
                    <div>
                        <label for="work_status" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Рабочий статус *
                        </label>
                        <select name="work_status" id="work_status" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}">
                                    {{ $status->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>





                    {{-- Должность --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Должность *
                        </label>

                        {{-- Видимое поле --}}
                        <input required type="text" id="position_search" placeholder="Выберите должность"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                  focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                  outline-none transition-colors text-[#060606]"
                            autocomplete="off"
                            value="{{ old('position_id') ? $positions->find(old('position_id'))->name ?? '' : '' }}">

                        {{-- Скрытое поле --}}
                        <input type="hidden" name="position_id" id="position_id" value="{{ old('position_id') }}">

                        {{-- Dropdown --}}
                        <ul id="position_list"
                            class="absolute z-20 mt-1 w-full bg-white border border-[#BFBFBF]
               rounded-lg max-h-72 overflow-auto hidden">
                            {{-- Кнопка очистить --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="" data-static="true">
                                Очистить
                            </li>

                            {{-- Список должностей (кроме начальников) --}}
                            @foreach ($positions as $pos)
                                @if (
                                    $pos->name !== 'Начальник комиссариата' &&
                                        $pos->name !== 'Начальник отдела' &&
                                        $pos->name !== 'Начальник отделения')
                                    <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $pos->id }}"
                                        data-name="{{ $pos->name }}">
                                        {{ $pos->name }}
                                        <span class="text-gray-400">(ID: {{ $pos->id }})</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>






                    <!-- Ставка -->
                    <div>
                        <label for="rate" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Ставка *
                        </label>
                        <select name="rate" id="rate"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]"
                            required>
                            @foreach ($rates as $rate)
                                <option value="{{ $rate }}" {{ old('rate', $rate) == 1 ? 'selected' : '' }}>
                                    {{ $rate }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- комиссариат --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Комиссариат *
                        </label>

                        {{-- visible input --}}
                        <input required type="text" id="commissariat_search" placeholder="Выберите комиссариат"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                  focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                  outline-none transition-colors text-[#060606]"
                            autocomplete="off" value="{{ $commissariat ? $commissariat->name : '' }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="commissariat_id" id="commissariat_id"
                            value="{{ $commissariat ? $commissariat->id : '' }}">

                        {{-- dropdown --}}
                        <ul id="commissariat_list"
                            class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF]
               rounded-lg max-h-72 overflow-auto hidden">

                            {{-- очистить --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="" data-static="true">
                                Очистить
                            </li>

                            @foreach ($commissariats as $commissariat)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $commissariat->id }}"
                                    data-name="{{ $commissariat->name }}">
                                    {{ $commissariat->name }}
                                    <span class="text-gray-400">(ID: {{ $commissariat->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- отдел --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Отдел
                        </label>

                        {{-- visible input --}}
                        <input type="text" id="department_search" placeholder="Выберите отдел"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                  focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                  outline-none transition-colors text-[#060606]"
                            autocomplete="off" value="{{ $department ? $department->name : '' }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="department_id" id="department_id"
                            value="{{ $department ? $department->id : '' }}">

                        {{-- dropdown --}}
                        <ul id="department_list"
                            class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF]
               rounded-lg max-h-72 overflow-auto hidden">

                            {{-- не выбирать --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="" data-static="true">
                                Не выбирать (самостоятельное отделение)
                            </li>

                            @foreach ($departments as $department)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $department->id }}"
                                    data-name="{{ $department->name }}"
                                    data-commissariat-id="{{ $department->commissariat->id }}"
                                    data-commissariat-name="{{ $department->commissariat->name }}">
                                    {{ $department->name }}
                                    <span class="text-gray-400">(ID: {{ $department->id }})</span>
                                    < {{ $department->commissariat->name }} </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- отделение --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Отделение
                        </label>

                        {{-- видимое поле --}}
                        <input type="text" id="division_search" placeholder="Выберите отделение"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
               outline-none transition-colors text-[#060606]"
                            autocomplete="off" value="{{ $division ? $division->name : '' }}">

                        <input type="hidden" name="division_id" id="division_id"
                            value="{{ $division ? $division->id : '' }}">

                        {{-- выпадающий список --}}
                        <ul id="division_list"
                            class="absolute z-10 mt-1 w-full bg-white border border-[#BFBFBF] rounded-lg max-h-72 overflow-auto hidden">

                            {{-- кнопка очистить --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="" data-static="true">
                                Не выбирать
                            </li>

                            {{-- список отделений --}}
                            @foreach ($divisions as $division)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $division->id }}"
                                    data-name="{{ $division->name }}"
                                    data-department-id="{{ $division?->department?->id }}"
                                    data-department-name="{{ $division?->department?->name }}"
                                    data-commissariat-id="{{ $division->commissariat->id }}"
                                    data-commissariat-name="{{ $division->commissariat->name }}">
                                    {{ $division->name }}
                                    @if ($division->department_id === null)
                                        (Самостоятельное отделение)
                                    @else
                                        < {{ $division?->department?->name }} @endif
                                            < {{ $division->commissariat->name }} </li>
                                    @endforeach
                        </ul>
                    </div>



                    <!-- самостоятельный -->
                    <div>
                        <label for="is_independent" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Самостоятельная должность *
                        </label>
                        <select name="is_independent" id="is_independent"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            <option value="0" {{ $isIndependent ? '' : 'selected' }}>Нет</option>
                            <option value="1" {{ $isIndependent ? 'selected' : '' }}>Да</option>
                        </select>
                    </div>



                    <!-- Кнопка отправки -->
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Создать сотрудника
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


{{--  телефоны и почта --}}
<script>
    function addEmail() {
        const wrapper = document.getElementById('emails-wrapper');
        wrapper.appendChild(createRow('email', 'emails[]', 'Введите почту'));
    }

    function addPhone() {
        const wrapper = document.getElementById('phones-wrapper');
        wrapper.appendChild(createRow('tel', 'phones[]', 'Введите телефон'));
    }

    function createRow(type, name, placeholder) {
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center';

        div.innerHTML = `
        <input type="${type}" name="${name}" placeholder="${placeholder}"
            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg">
        <button type="button" onclick="removeRow(this)"
            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
            ✕
        </button>
    `;

        return div;
    }

    function removeRow(button) {
        button.parentElement.remove();
    }
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
        // --- элементы ---
        const commissariatInput = document.getElementById('commissariat_search');
        const commissariatHidden = document.getElementById('commissariat_id');
        const commissariatList = document.getElementById('commissariat_list');

        const departmentInput = document.getElementById('department_search');
        const departmentHidden = document.getElementById('department_id');
        const departmentList = document.getElementById('department_list');

        const divisionInput = document.getElementById('division_search');
        const divisionHidden = document.getElementById('division_id');
        const divisionList = document.getElementById('division_list');

        // --- универсальная фильтрация ---
        function filterList(input, list) {
            const query = input.value.toLowerCase().trim();
            const items = list.querySelectorAll('li');
            let hasVisible = false;

            items.forEach(item => {
                if (item.dataset.static === 'true') {
                    item.classList.remove('hidden');
                    hasVisible = true;
                    return;
                }
                const name = (item.dataset.name || '').toLowerCase();
                if (!query || name.includes(query)) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            list.classList.toggle('hidden', !hasVisible);
        }

        // --- КЛИК ПО ЭЛЕМЕНТАМ ---
        function handleItemClick(item, input, hidden, extra = {}) {
            if (item.dataset.static === 'true') {
                input.value = '';
                hidden.value = '';
                if (extra.clearFields) extra.clearFields.forEach(f => {
                    f.value = '';
                });
                return true; // был очисткой
            }
            input.value = item.dataset.name || '';
            hidden.value = item.dataset.id || '';
            if (extra.setFields) {
                extra.setFields.forEach(f => {
                    f.input.value = f.name || '';
                    f.hidden.value = f.id || '';
                });
            }
            return false;
        }

        // --- КЛИКИ ---
        // Комиссариат
        commissariatList.querySelectorAll('li').forEach(item => {
            item.addEventListener('click', () => {
                const cleared = handleItemClick(item, commissariatInput, commissariatHidden, {
                    clearFields: [departmentInput, departmentHidden, divisionInput,
                        divisionHidden
                    ]
                });
                commissariatList.classList.add('hidden');
            });
        });

        // Отдел
        departmentList.querySelectorAll('li').forEach(item => {
            item.addEventListener('click', () => {
                const cleared = handleItemClick(item, departmentInput, departmentHidden, {
                    setFields: [{
                        input: commissariatInput,
                        hidden: commissariatHidden,
                        name: item.dataset.commissariatName,
                        id: item.dataset.commissariatId
                    }],
                    clearFields: [divisionInput, divisionHidden]
                });
                departmentList.classList.add('hidden');
            });
        });

        // Отделение
        divisionList.querySelectorAll('li').forEach(item => {
            item.addEventListener('click', () => {
                const cleared = handleItemClick(item, divisionInput, divisionHidden, {
                    setFields: [{
                        input: commissariatInput,
                        hidden: commissariatHidden,
                        name: item.dataset.commissariatName,
                        id: item.dataset.commissariatId
                    }]
                });
                // Отдел может быть пустым если самостоятельное
                if (item.dataset.departmentId && item.dataset.departmentName) {
                    departmentInput.value = item.dataset.departmentName;
                    departmentHidden.value = item.dataset.departmentId;
                } else {
                    departmentInput.value = '';
                    departmentHidden.value = '';
                }
                divisionList.classList.add('hidden');
            });
        });

        // --- Фокус и фильтрация ---
        [
            [commissariatInput, commissariatList],
            [departmentInput, departmentList],
            [divisionInput, divisionList]
        ].forEach(([input, list]) => {
            input.addEventListener('focus', () => filterList(input, list));
            input.addEventListener('input', () => {
                const hidden = document.getElementById(input.id.replace('_search', '_id'));
                hidden.value = '';
                filterList(input, list);
            });
        });

        // --- Очистка зависимых полей при ручной очистке ---
        commissariatInput.addEventListener('input', () => {
            if (!commissariatInput.value) {
                departmentInput.value = '';
                departmentHidden.value = '';
                divisionInput.value = '';
                divisionHidden.value = '';
            }
        });

        departmentInput.addEventListener('input', () => {
            if (!departmentInput.value) {
                divisionInput.value = '';
                divisionHidden.value = '';
            }
        });

        // --- Закрытие списков при клике вне ---
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                commissariatList.classList.add('hidden');
                departmentList.classList.add('hidden');
                divisionList.classList.add('hidden');
            }
        });
    });
</script>
