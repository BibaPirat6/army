@extends('layouts.main')

@section('header-title')
    Редактирование отделения
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('divisions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Редактирование отделения</h1>
            <p class="text-[#565A5B] mt-1">Заполните все необходимые поля для редактирования отделения</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('divisions.update', ['id' => $division->id]) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    <!-- Название отделения -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название отделения *
                        </label>
                        <input type="text" name="name" id="name" placeholder="Название отделения"
                            value="{{ old('name', $division->name) }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
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
                            autocomplete="off"
                            value="{{ old('department_id', $division?->department?->id ? $division?->department?->name : '') }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="department_id" id="department_id2"
                            value="{{ old('department_id', $division?->department?->id) }}">

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
                                    data-commissariat-id="{{ $department->commissariat->id ?? '' }}"
                                    data-commissariat-name="{{ $department->commissariat->name ?? '' }}">
                                    {{ $department->name }}
                                    <span class="text-gray-400">(ID: {{ $department->id }})</span>
                                    < {{ $department->commissariat->name ?? '' }} </li>
                            @endforeach

                        </ul>
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
                            autocomplete="off"
                            value="{{ old('commissariat_id', $division->commissariat->id ? $division->commissariat->name : '') }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="commissariat_id" id="commissariat_id2"
                            value="{{ old('commissariat_id', $division->commissariat->id) }}">

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

                    {{-- старый начальник --}}
                    <input type="hidden" name="old_chief_employee_id"
                        value="{{ old('old_chief_employee_id', $division->getChiefAttribute() ? $division->getChiefAttribute()->id : '') }}">

                    {{-- должность начальника отдела (исправлено) --}}
                    <input type="hidden" name="chief_position_id"
                        value="{{ old('chief_position_id', $division->chiefCommissariatPosition?->position?->id) }}">

                    {{-- начальник --}}
                    <div class="relative" id="chief-wrapper">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Начальник
                        </label>

                        {{-- visible input --}}
                        <input type="text" id="chief_employee_search" placeholder="Начните вводить ФИО"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
               outline-none transition-colors text-[#060606]"
                            autocomplete="off"
                            value="{{ old('chief_employee_id', $division->getChiefAttribute() ? $division->getChiefAttribute()->getFullNameAttribute() : '') }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="chief_employee_id" id="chief_employee_id"
                            value="{{ old('chief_employee_id', $division->getChiefAttribute() ? $division->getChiefAttribute()->id : '') }}"
                            data-original="{{ $division->getChiefAttribute() ? $division->getChiefAttribute()->id : '' }}"
                            data-original-exists="{{ $division->getChiefAttribute() ? '1' : '0' }}">

                        {{-- dropdown --}}
                        <ul id="chief_employee_list"
                            class="relative z-50 mt-1 w-full bg-white border border-[#BFBFBF]
               rounded-lg max-h-72 overflow-auto hidden">

                            {{-- Очистить --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать" data-static="true">
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

                        {{-- Блок статуса для предыдущего начальника (показывается при снятии/смене) --}}
                        <div id="chief_status_wrapper" class="mt-3 hidden">
                            <label for="old_chief_employee_position_status_id"
                                class="block text-sm font-medium text-[#565A5B] mb-2">
                                Статус назначения *
                            </label>

                            <select name="old_chief_employee_position_status_id"
                                id="old_chief_employee_position_status_id"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                <option value="">— выберите статус —</option>
                                @foreach ($employeePositionStatuses as $status)
                                    @if ($status->id != 1)
                                        @php
                                            $selected = old('old_chief_employee_position_status_id') == $status->id;
                                        @endphp
                                        <option value="{{ $status->id }}" {{ $selected ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
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
                            Изменить отделение
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection



<script>
    document.addEventListener('DOMContentLoaded', function() {

        const wrapper = document.getElementById('chief-wrapper'); // ИЗМЕНИЛИ: ищем по wrapper
        const input = document.getElementById('chief_employee_search');
        const hiddenInput = document.getElementById('chief_employee_id');
        const list = document.getElementById('chief_employee_list');

        if (!input || !hiddenInput || !list) return;

        const items = list.querySelectorAll('li');

        const statusWrapper = document.getElementById('chief_status_wrapper');

        const originalId = hiddenInput.dataset.original || '';
        const originalExists = hiddenInput.dataset.originalExists === '1';

        function showList() {
            list.classList.remove('hidden');
        }

        function hideList() {
            list.classList.add('hidden');
        }

        function updateStatusBlock() {
            const selectedId = hiddenInput.value || '';

            // Показываем блок если был начальник И (поле пустое ИЛИ выбран другой)
            const shouldShow = originalExists && (!selectedId || selectedId !== originalId);

            statusWrapper.classList.toggle('hidden', !shouldShow);
        }

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
                const id = item.dataset.id;

                if (query === '' || name.includes(query) || id.includes(query)) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            list.classList.toggle('hidden', !hasVisible);
        }

        // --- EVENTS ---

        input.addEventListener('focus', () => {
            showList();
            filterList(input.value);
        });

        input.addEventListener('input', () => {
            hiddenInput.value = '';
            updateStatusBlock(); // Сразу показываем блок при вводе
            showList();
            filterList(input.value);
        });

        items.forEach(item => {
            item.addEventListener('click', () => {

                // --- ОЧИСТИТЬ (Не назначать) ---
                if (item.dataset.static === 'true') {
                    input.value = '';
                    hiddenInput.value = '';
                    updateStatusBlock(); // Сразу показываем блок статуса
                    hideList();
                    return;
                }

                // --- ВЫБОР СОТРУДНИКА ---
                const id = item.dataset.id;
                const name = item.dataset.name;

                input.value = name;
                hiddenInput.value = id;

                updateStatusBlock();
                hideList();
            });
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) { // ИЗМЕНИЛИ: проверяем wrapper
                hideList();
            }
        });

        // init
        updateStatusBlock();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('commissariat_search');
        const hiddenInput = document.getElementById('commissariat_id2');
        const list = document.getElementById('commissariat_list');
        const items = list.querySelectorAll('li');

        function showList() {
            list.classList.remove('hidden');
        }

        function hideList() {
            list.classList.add('hidden');
        }

        function filterList(value) {
            const query = value.toLowerCase().trim();
            let hasVisible = false;

            items.forEach(item => {
                if (item.dataset.static === 'true') {
                    item.classList.remove('hidden');
                    hasVisible = true;
                    return;
                }

                const name = item.dataset.name?.toLowerCase() || '';

                if (query === '' || name.includes(query)) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            list.classList.toggle('hidden', !hasVisible);
        }

        input.addEventListener('focus', () => {
            showList();
            filterList(input.value);
        });

        input.addEventListener('input', () => {
            hiddenInput.value = '';
            showList();
            filterList(input.value);
        });

        items.forEach(item => {
            item.addEventListener('click', () => {


                if (item.dataset.static === 'true') {
                    input.value = '';
                    hiddenInput.value = '';
                    hideList();
                    return;
                }


                input.value = item.dataset.name;
                hiddenInput.value = item.dataset.id;
                hideList();
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                hideList();
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const departmentInput = document.getElementById('department_search');
        const departmentHidden = document.getElementById('department_id2');
        const departmentList = document.getElementById('department_list');
        const departmentItems = departmentList.querySelectorAll('li');

        const commissariatInput = document.getElementById('commissariat_search');
        const commissariatHidden = document.getElementById('commissariat_id2');
        const commissariatList = document.getElementById('commissariat_list');
        const commissariatItems = commissariatList.querySelectorAll('li');

        // Универсальная фильтрация списка
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

        // --- ОТДЕЛ ---
        departmentItems.forEach(item => {
            item.addEventListener('click', () => {
                if (item.dataset.static === 'true') {
                    // Очистка отдела и связанного комиссариата
                    departmentInput.value = '';
                    departmentHidden.value = '';
                    commissariatInput.value = '';
                    commissariatHidden.value = '';
                    departmentList.classList.add('hidden');
                    return;
                }

                departmentInput.value = item.dataset.name;
                departmentHidden.value = item.dataset.id;
                departmentList.classList.add('hidden');

                // Подставляем связанный комиссариат
                commissariatInput.value = item.dataset.commissariatName || '';
                commissariatHidden.value = item.dataset.commissariatId || '';
            });
        });

        departmentInput.addEventListener('focus', () => filterList(departmentInput, departmentList));
        departmentInput.addEventListener('input', () => {
            departmentHidden.value = '';
            commissariatInput.value = '';
            commissariatHidden.value = '';
            filterList(departmentInput, departmentList);
        });

        // --- КОМИССАРИАТ ---
        commissariatItems.forEach(item => {
            item.addEventListener('click', () => {
                if (item.dataset.static === 'true') {
                    // Очистка комиссариата и связанного отдела
                    commissariatInput.value = '';
                    commissariatHidden.value = '';
                    departmentInput.value = '';
                    departmentHidden.value = '';
                    commissariatList.classList.add('hidden');
                    return;
                }

                commissariatInput.value = item.dataset.name;
                commissariatHidden.value = item.dataset.id;
                commissariatList.classList.add('hidden');

                // При выборе вручную не трогаем отдел, только если есть связь
            });
        });

        commissariatInput.addEventListener('focus', () => filterList(commissariatInput, commissariatList));
        commissariatInput.addEventListener('input', () => {
            commissariatHidden.value = '';
            // Очищаем отдел, если удаляем комиссариат вручную
            if (commissariatInput.value === '') {
                departmentInput.value = '';
                departmentHidden.value = '';
            }
            filterList(commissariatInput, commissariatList);
        });

        // --- Закрытие списков при клике вне ---
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                departmentList.classList.add('hidden');
                commissariatList.classList.add('hidden');
            }
        });
    });
</script>
