@extends('layouts.main')

@section('header-title')
    Изменение отдела
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('departments.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Изменение отдела</h1>
            <p class="text-[#565A5B] mt-1">Редактирование отдела: "{{ $department->name }}"</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('departments.update', $department->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    <!-- Название отдела -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название отдела
                        </label>
                        <input type="text" name="name" id="name" placeholder="Название отдела"
                            value="{{ old('name', $department->name) }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    {{-- комиссариат --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Комиссариат *
                        </label>

                        {{-- visible input --}}
                        <input type="text" id="commissariat_search" placeholder="Выберите комиссариат" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                        focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                        outline-none transition-colors text-[#060606]" autocomplete="off"
                            value="{{ old('commissariat_name', $department->commissariat?->name ?? '') }}" required>

                        {{-- hidden value --}}
                        <input type="hidden" name="commissariat_id" id="commissariat_id"
                            value="{{ old('commissariat_id', $department->commissariat_id) }}">

                        {{-- dropdown --}}
                        <ul id="commissariat_list" class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF]
                                    rounded-lg max-h-72 overflow-auto hidden">

                            {{-- очистить --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id="" data-name=""
                                data-static="true">
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
                        value="{{  old('old_chief_employee_id', $department->getChiefAttribute() ? $department->getChiefAttribute()->id : '') }}">

                    {{-- должность начальника отдела --}}
                    <input type="hidden" value="{{  $department->chiefCommissariatPosition?->activeAssignment?->commissariatPosition?->position?->id }}" name="chief_position_id">

                    {{-- начальник --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Начальник *
                        </label>

                        {{-- visible input (необязательное) --}}
                        <input required type="text" id="chief_employee_search" placeholder="Начните вводить ФИО" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                               outline-none transition-colors text-[#060606]" autocomplete="off"
                            value="{{ old('chief_employee_id', $department->getChiefAttribute() ? $department->getChiefAttribute()->getFullNameAttribute() : '') }}">

                        {{-- hidden value (необязательное) --}}
                        <input required type="hidden" name="chief_employee_id" id="chief_employee_id"
                            value="{{ old('chief_employee_id', $department->getChiefAttribute() ? $department->getChiefAttribute()->id : '') }}"
                            data-original="{{ $department->getChiefAttribute() ? $department->getChiefAttribute()->id : '' }}"
                            data-original-exists="{{ $department->getChiefAttribute() ? '1' : '0' }}">
                        {{-- dropdown --}}
                        <ul id="chief_employee_list" class="absolute z-50 mt-1 w-full bg-white border border-[#BFBFBF]
                       rounded-lg max-h-72 overflow-auto hidden">

                            {{-- Не назначать --}}
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


                        {{-- Поля штатной должности (всегда видимы) для редактирования --}}
                        @php
                            $chiefSlot = $department->chiefCommissariatPosition ?? null;
                            $currentAssignment = $chiefSlot ? $chiefSlot->activeAssignment : null;
                        @endphp


                        {{-- блок выбора статуса — показывается только при смене начальника (если ранее начальник был) --}}
                        <div id="chief_status_wrapper" class="mt-3 hidden">
                            <label for="old_chief_employee_position_status_id"
                                class="block text-sm font-medium text-[#565A5B] mb-2">
                                Статус назначения *
                            </label>

                            <!-- Дополнительные поля для предыдущего начальника (появляются при смене) -->
                            <div id="previous_assignment_fields"
                                class="mt-3 hidden bg-white p-4 rounded-lg border border-[#E5E7EB]">
                                <h4 class="text-sm font-medium text-[#565A5B] mb-2">Данные для предыдущего начальника</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <select name="old_chief_employee_position_status_id"
                                        id="old_chief_employee_position_status_id"
                                        class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                        <option value="">— выберите статус —</option>
                                        @foreach($employeePositionStatuses as $status)
                                            {{-- 🔥 Исключаем статус "работает" (ID = 1) из списка причин смены --}}
                                            @if($status->id != 1)
                                                @php
                                                    $selected = old('old_chief_employee_position_status_id')
                                                        ? (old('old_chief_employee_position_status_id') == $status->id)
                                                        : ($currentAssignment?->employee_position_status_id == $status->id);
                                                @endphp
                                                <option value="{{ $status->id }}" {{ $selected ? 'selected' : '' }}>
                                                    {{ $status->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>

                                </div>
                            </div>
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
                            Обновить отдел
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const input = document.getElementById('chief_employee_search');
        const hiddenInput = document.getElementById('chief_employee_id');
        const list = document.getElementById('chief_employee_list');

        if (!input || !hiddenInput || !list) return;

        const items = list.querySelectorAll('li');

        const statusWrapper = document.getElementById('chief_status_wrapper');
        const previousFields = document.getElementById('previous_assignment_fields');

        const originalId = hiddenInput.dataset.original || '';
        const originalExists = hiddenInput.dataset.originalExists === '1';

        function showList() {
            list.classList.remove('hidden');
        }

        function hideList() {
            list.classList.add('hidden');
        }

        function updatePreviousBlock() {
            const selectedId = hiddenInput.value || '';

            const shouldShow = originalExists && selectedId && selectedId !== originalId;

            statusWrapper.classList.toggle('hidden', !shouldShow);
            previousFields.classList.toggle('hidden', !shouldShow);
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
            updatePreviousBlock();
            showList();
            filterList(input.value);
        });

        items.forEach(item => {
            item.addEventListener('click', () => {

                // --- НЕ НАЗНАЧАТЬ ---
                if (item.dataset.static === 'true') {
                    input.value = '';
                    hiddenInput.value = '';
                    updatePreviousBlock();
                    hideList();
                    return;
                }

                // --- ВЫБОР СОТРУДНИКА ---
                const id = item.dataset.id;
                const name = item.dataset.name;

                input.value = name;
                hiddenInput.value = id;

                updatePreviousBlock();
                hideList();
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                hideList();
            }
        });

        // init
        updatePreviousBlock();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('commissariat_search');
        const hiddenInput = document.getElementById('commissariat_id');
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
        const input = document.getElementById('chief_position_search');
        const hiddenInput = document.getElementById('chief_position_id');
        const list = document.getElementById('chief_position_list');
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

        // Инициализация: если в скрытом поле уже есть значение (из БД), выберем соответствующий элемент
        (function initializeSelected() {
            const currentId = (hiddenInput.value || '').toString();
            let selectedLi = null;

            if (currentId) {
                selectedLi = list.querySelector(`li[data-id="${currentId}"]`);
            }

            // fallback: найдем li с data-selected="1"
            if (!selectedLi) {
                selectedLi = list.querySelector('li[data-selected="1"]');
            }

            if (selectedLi) {
                // установим видимое значение и пометим элемент
                input.value = selectedLi.dataset.name || input.value;
                if (!currentId && selectedLi.dataset.id) {
                    hiddenInput.value = selectedLi.dataset.id;
                }
                selectedLi.classList.add('bg-gray-100');
                // прокрутим в центр видимости
                try { selectedLi.scrollIntoView({ block: 'center' }); } catch (e) { }
            }
        })();

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
                    // очистим подсветку
                    items.forEach(i => i.classList.remove('bg-gray-100'));
                    hideList();
                    return;
                }

                // при клике переключаем подсветку
                items.forEach(i => i.classList.remove('bg-gray-100'));
                item.classList.add('bg-gray-100');

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