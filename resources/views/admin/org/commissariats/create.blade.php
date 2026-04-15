@extends('layouts.main')

@section('header-title')
    Добавление коммиссариата
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('commissariats.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Добавление комиссариата</h1>
            <p class="text-[#565A5B] mt-1">Заполните все необходимые поля для создания нового комиссариата</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('commissariats.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    <!-- Название комиссариата -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название комиссариата *
                        </label>
                        <input type="text" name="name" id="name" placeholder="Название комиссариата"
                            value="{{ old('name') }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    {{-- начальник --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Начальник (опционально — поле можно оставить пустым)
                        </label>

                        {{-- visible input (необязательное) --}}
                        <input type="text" id="chief_employee_search" placeholder="Начните вводить ФИО" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                       focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                       outline-none transition-colors text-[#060606]" autocomplete="off">

                        {{-- hidden value (необязательное) --}}
                        <input type="hidden" name="chief_employee_id" id="chief_employee_id"
                            value="{{ old('chief_employee_id') }}">

                        {{-- dropdown --}}
                        <ul id="chief_employee_list" class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF]
                       rounded-lg max-h-72 overflow-auto hidden">

                            {{-- опция "Не назначать" --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать" data-static="true">
                                Не назначать
                            </li>

                            @foreach ($employees as $employee)
                                                    <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $employee->id }}" data-name="{{ trim(
                                    $employee->getFullNameAttribute()
                                ) }}" data-search="{{ $employee->id }}">
                                                        @if ($employee->person)
                                                            {{ $employee->getFullNameAttribute()}}
                                                            <span class="text-gray-400">(ID: {{ $employee->id ?? '*' }})</span>
                                                        @else
                                                            <span class="text-gray-400">Без ФИО (ID: {{ $employee->id }})</span>
                                                        @endif
                                                    </li>
                            @endforeach
                        </ul>


                        {{-- Поля штатной должности (всегда видимы) --}}
                        <div class="mt-4 bg-white p-4 rounded-lg border border-[#E5E7EB]">
                            <h3 class="text-sm font-medium text-[#565A5B] mb-2">Штатная должность (параметры)</h3>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="rate_total" class="block text-sm font-medium text-[#565A5B] mb-2">Ставка
                                        (rate_total)</label>
                                    <input type="number" step="0.25" min="0.25" max="2.00" name="rate_total" id="rate_total" required
                                        value="{{ old('rate_total', '1.00') }}"
                                        class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                </div>

                                <div class="flex items-end">
                                    <label class="inline-flex items-center cursor-pointer">
                                        {{-- 1. Скрытое поле со значением 0 (отправляется всегда) --}}
                                        <input type="hidden" name="is_independent" value="0">
                                        
                                        {{-- 2. Чекбокс со значением 1 (перезапишет 0, если отмечен) --}}
                                        <input type="checkbox" 
                                            name="is_independent" 
                                            id="is_independent" 
                                            value="1" 
                                            class="mr-2 rounded border-gray-300 text-[#A60644] focus:ring-[#A60644]" 
                                            {{ old('is_independent') ? 'checked' : '' }}>
                                            
                                        <span class="text-sm text-[#565A5B]">Самостоятельная должность (is_independent)</span>
                                    </label>
                                </div>
                            </div>
                        </div>


                    </div>

                    {{-- Параметры назначения начальника (показываются только если выбран начальник) --}}
                    <div id="chief_assignment_fields" class="hidden mt-4 bg-white p-4 rounded-lg border border-[#E5E7EB]">
                        <h3 class="text-sm font-medium text-[#565A5B] mb-2">Параметры назначения (только если выбран
                            начальник)</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="rate" class="block text-sm font-medium text-[#565A5B] mb-2">Ставка сотрудника
                                    (rate)</label>
                                <input type="number" step="0.25" min="0.25" max="2.00" name="rate" id="rate"
                                    value="{{ old('rate', '1.00') }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            </div>

                            <div>
                                <label for="employee_position_status_id"
                                    class="block text-sm font-medium text-[#565A5B] mb-2">Статус назначения</label>
                                <select name="employee_position_status_id" id="employee_position_status_id"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                    @foreach($employeePositionStatuses as $status)
                                        @php
                                            $selected = false;
                                            if (old('employee_position_status_id')) {
                                                $selected = old('employee_position_status_id') == $status->id;
                                            } else {
                                                // выбрать статус "работает" по умолчанию если есть, иначе id==1
                                                $lower = mb_strtolower($status->name);
                                                $selected = (str_contains($lower, 'работ') || $status->id == 1) && !isset($selected);
                                            }
                                        @endphp
                                        <option value="{{ $status->id }}" {{ $selected ? 'selected' : '' }}>{{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="started_at" class="block text-sm font-medium text-[#565A5B] mb-2">Дата
                                    начала</label>
                                <input type="date" name="started_at" id="started_at"
                                    value="{{ old('started_at', \Carbon\Carbon::now()->toDateString()) }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            </div>

                            <div>
                                <label for="expected_return_at"
                                    class="block text-sm font-medium text-[#565A5A] mb-2">Ожидаемое возвращение (только для
                                    отпуск/декрет)</label>
                                <input type="date" name="expected_return_at" id="expected_return_at"
                                    value="{{ old('expected_return_at') }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            </div>

                            <div>
                                <label for="ended_at" class="block text-sm font-medium text-[#565A5A] mb-2">Дата увольнения
                                    (только для статус "уволен")</label>
                                <input type="date" name="ended_at" id="ended_at"
                                    value="{{ old('ended_at', \Carbon\Carbon::now()->toDateString()) }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            </div>
                        </div>
                    </div>

                    <!-- x -->
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по горизонтали *
                        </label>
                        <input required type="number" name="longitude" id="longitude" placeholder="Ось х" max="200" min="1"
                            value="{{ old('longitude', $x) }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- y -->
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по вертикали *
                        </label>
                        <input required type="number" name="latitude" id="latitude" placeholder="Ось y" max="120" min="1"
                            value="{{ old('latitude', $y) }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
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
                            Создать комиссариат
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('chief_employee_search');
        const hiddenInput = document.getElementById('chief_employee_id');
        const list = document.getElementById('chief_employee_list');
        const items = list.querySelectorAll('li');

        const assignmentBlock = document.getElementById('chief_assignment_fields');
        const statusSelect = document.getElementById('employee_position_status_id');
        const expectedReturn = document.getElementById('expected_return_at');
        const endedAt = document.getElementById('ended_at');

        function showList() { list.classList.remove('hidden'); }
        function hideList() { list.classList.add('hidden'); }

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
                const id = item.dataset.id || '';

                if (query === '') {
                    item.classList.remove('hidden');
                    hasVisible = true;
                    return;
                }

                if (name.includes(query) || id.includes(query)) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            list.classList.toggle('hidden', !hasVisible);
        }

        function updateAssignmentVisibility() {
            // show assignment fields only when a chief id (non-empty) is selected
            const hasChief = !!(hiddenInput.value && hiddenInput.value.trim() !== '');
            assignmentBlock.classList.toggle('hidden', !hasChief);
        }

        function updateDynamicFieldsVisibility() {
            if (!statusSelect) return;
            const selectedText = statusSelect.options[statusSelect.selectedIndex]?.text?.toLowerCase() || '';

            // expected return for отпуск/декрет
            const showExpectedReturn = selectedText.includes('отпуск') || selectedText.includes('декрет');
            if (expectedReturn) {
                expectedReturn.closest('div').classList.toggle('hidden', !showExpectedReturn);
                if (!showExpectedReturn) expectedReturn.value = '';
            }

            // ended_at for уволен
            const showEndedAt = selectedText.includes('увол'); // покрывает 'уволен', 'увольнение' и т.п.
            if (endedAt) {
                endedAt.closest('div').classList.toggle('hidden', !showEndedAt);
                if (!showEndedAt) endedAt.value = '';
            }
        }

        // initial state (useful when returning with validation errors)
        updateAssignmentVisibility();
        if (statusSelect) updateDynamicFieldsVisibility();

        input.addEventListener('focus', () => { showList(); filterList(input.value); });

        input.addEventListener('input', () => {
            hiddenInput.value = '';
            updateAssignmentVisibility();
            showList();
            filterList(input.value);
        });

        items.forEach(item => {
            item.addEventListener('click', () => {
                if (item.dataset.static === 'true') {
                    const wasNotEmpty = input.value.trim() !== '' || hiddenInput.value !== '';
                    input.value = '';
                    hiddenInput.value = '';
                    updateAssignmentVisibility();

                    if (wasNotEmpty) { showList(); filterList(''); }
                    else { hideList(); }
                    return;
                }

                input.value = item.dataset.name || `ID ${item.dataset.id}`;
                hiddenInput.value = item.dataset.id;
                updateAssignmentVisibility();
                hideList();
            });
        });

        document.addEventListener('click', (e) => { if (!e.target.closest('.relative')) hideList(); });

        if (statusSelect) {
            statusSelect.addEventListener('change', updateDynamicFieldsVisibility);
        }
    });
</script>