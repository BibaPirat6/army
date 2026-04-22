@extends('layouts.main')

@section('header-title')
    Редактирование назначения сотрудника
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('commissariat-positions.index', ['commissariat_id' => $commissariatPosition->commissariat_id]) }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Редактирование назначения</h1>
            <p class="text-[#565A5B] mt-1">
                Должность: <span class="font-semibold text-[#A60644]">{{ $commissariatPosition->position->name }}</span>
            </p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('commissariat-positions.assign.update', [
                    'id' => $commissariatPosition->id,
                    'employeePositionId' => $employeePosition->id
                ]) }}"
                    method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="back_url" value="{{ $backUrl }}">

                    <!-- Информация о свободных ставках -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-blue-700">
                                    Всего ставок: <strong>{{ number_format($commissariatPosition->rate_total, 2) }}</strong> | 
                                    Занято: <strong>{{ number_format($occupiedRate, 2) }}</strong> | 
                                    Свободно: <strong>{{ number_format($availableRate, 2) }}</strong>
                                </p>
                                <p class="text-xs text-blue-600 mt-1">
                                    Текущая ставка сотрудника: {{ number_format($employeePosition->rate, 2) }}
                                    @if($employeePosition->employeePositionStatus->occupies_rate)
                                        <span class="text-green-600">(занимает ставку)</span>
                                    @else
                                        <span class="text-orange-600">(не занимает ставку)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Сотрудник -->
                    <div class="relative" id="chief-select">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Сотрудник <span class="text-red-500">*</span>
                        </label>

                        <input type="text" id="chief_employee_search" 
                            placeholder="Начните вводить ФИО" 
                            autocomplete="off"
                            value="{{ $employeePosition->employee->getFullNameAttribute() }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none">

                        <input type="hidden" name="chief_employee_id" id="chief_employee_id" 
                            value="{{ $employeePosition->employee_id }}">

                        <ul id="chief_employee_list" class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF]
                               rounded-lg max-h-72 overflow-auto hidden shadow-lg">

                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать">
                                Очистить
                            </li>

                            @foreach ($employees as $employee)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" 
                                    data-id="{{ $employee->id }}"
                                    data-name="{{ trim($employee->getFullNameAttribute()) }}"
                                    {{ $employee->id == $employeePosition->employee_id ? 'style=background-color:#f0f0f0' : '' }}>
                                    {{ $employee->getFullNameAttribute() }}
                                    <span class="text-gray-400">(ID: {{ $employee->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                        @error('chief_employee_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ставка -->
                    <div id="rate-field">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Ставка <span class="text-red-500">*</span>
                        </label>
                       <input type="number" 
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]" 
                            autocomplete="off"
                            placeholder="Введите ставку" 
                            value="{{ old('rate', $employeePosition->rate) }}" 
                            min="0.25" 
                            max="{{ $maxRateForInput }}" 
                            step="0.25" 
                            name="rate"
                            required
                        >
                        @error('rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Минимум: 0.25, Максимум: {{ number_format($availableRate + ($employeePosition->employeePositionStatus->occupies_rate ? $employeePosition->rate : 0), 2) }}, шаг: 0.25
                        </p>
                    </div>

                    <!-- Статус сотрудника на должности -->
                    <div>
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Статус <span class="text-red-500">*</span>
                        </label>
                        <select name="employee_position_status_id" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors">
                            <option value="">Выберите статус</option>
                            @foreach ($employeePositionStatuses as $status)
                                <option value="{{ $status->id }}" 
                                    data-occupies-rate="{{ $status->occupies_rate }}"
                                    {{ old('employee_position_status_id', $employeePosition->employee_position_status_id) == $status->id ? 'selected' : '' }}
                                    style="border-left-color: {{ $status->color }}; border-left-width: 3px;">
                                    {{ $status->name }}
                                    @if(!$status->occupies_rate)
                                        (не занимает ставку)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('employee_position_status_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Кнопка отправки -->
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('chief-select');
        const input = container.querySelector('#chief_employee_search');
        const hidden = container.querySelector('#chief_employee_id');
        const list = container.querySelector('#chief_employee_list');
        const items = list.querySelectorAll('li');
        const rateField = document.getElementById('rate-field');
        const rateInput = document.querySelector('input[name="rate"]');
        const statusSelect = document.querySelector('select[name="employee_position_status_id"]');
        
        // Сохраняем оригинальные значения
        const originalRate = parseFloat(rateInput?.value || 0);
        const originalStatusId = statusSelect?.value;
        const originalEmployeeId = hidden.value;
        
        // Получаем максимальную ставку из атрибута
        let maxRate = parseFloat(rateInput?.getAttribute('max') || 0);
        
        // Сохраняем значение ставки для случаев, когда статус не занимает ставку
        let savedRateValue = originalRate;

        // Функция для показа/скрытия поля ставки
        function toggleRateField() {
            if (hidden.value && hidden.value !== '') {
                rateField.style.display = 'block';
            } else {
                rateField.style.display = 'none';
                if (rateInput) {
                    rateInput.value = '';
                }
            }
        }

        // Функция обновления состояния поля ставки в зависимости от статуса
        function updateRateFieldState() {
            const selectedOption = statusSelect?.options[statusSelect.selectedIndex];
            const occupiesRate = selectedOption?.dataset.occupiesRate === '1';
            
            if (!rateInput) return;
            
            if (occupiesRate) {
                // Статус занимает ставку - поле активно, можно редактировать
                rateInput.disabled = false;
                rateInput.required = true;
                rateInput.max = maxRate;
                
                // Восстанавливаем сохраненное значение ставки, если оно было
                if (savedRateValue > 0) {
                    rateInput.value = savedRateValue;
                } else if (!rateInput.value || rateInput.value == 0) {
                    rateInput.value = 0.25;
                }
                
                // Проверяем, что значение не превышает max
                let currentValue = parseFloat(rateInput.value);
                if (currentValue > maxRate) {
                    rateInput.value = maxRate;
                    savedRateValue = maxRate;
                }
                if (currentValue < 0.25) {
                    rateInput.value = 0.25;
                    savedRateValue = 0.25;
                }
            } else {
                // Статус НЕ занимает ставку - поле отключено, но сохраняем значение
                rateInput.disabled = true;
                rateInput.required = false;
                // Сохраняем текущее значение перед отключением
                if (rateInput.value && parseFloat(rateInput.value) > 0) {
                    savedRateValue = parseFloat(rateInput.value);
                }
                // Показываем сохраненное значение (оно не будет отправлено на сервер, т.к. поле disabled)
                rateInput.value = savedRateValue;
            }
        }

        // Функция проверки ставки при ручном вводе
        function validateRate() {
            if (!rateInput || rateInput.disabled) return;
            
            let value = parseFloat(rateInput.value);
            if (isNaN(value)) value = 0.25;
            if (value < 0.25) {
                rateInput.value = 0.25;
                savedRateValue = 0.25;
                alert('Минимальная ставка: 0.25');
            }
            if (value > maxRate) {
                rateInput.value = maxRate;
                savedRateValue = maxRate;
                alert('Максимальная доступная ставка: ' + maxRate.toFixed(2));
            } else {
                savedRateValue = parseFloat(rateInput.value);
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
                item.style.display = (!q || name.includes(q) || id.includes(q)) ? 'block' : 'none';
            });
        }

        // Обработчики для поиска сотрудника
        input.addEventListener('focus', () => {
            open();
            filter(input.value);
        });

        input.addEventListener('input', () => {
            hidden.value = '';
            open();
            filter(input.value);
            toggleRateField();
        });

        items.forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.id || '';
                const name = item.dataset.name || '';
                input.value = name;
                hidden.value = id;
                close();
                toggleRateField();
                updateRateFieldState();
            });
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                close();
            }
        });

        // Валидация ставки
        if (rateInput) {
            rateInput.addEventListener('change', validateRate);
            rateInput.addEventListener('input', validateRate);
        }

        // Обработчик изменения статуса
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const occupiesRate = selectedOption?.dataset.occupiesRate === '1';
                
                if (!occupiesRate) {
                    // Статус не занимает ставку - просто отключаем поле, но не меняем значение
                    if (confirm('Выбранный статус НЕ занимает ставку.\n\nСтавка будет сохранена, но не будет влиять на занятость.\nПродолжить?')) {
                        updateRateFieldState();
                    } else {
                        // Возвращаем предыдущий статус
                        this.value = originalStatusId;
                        updateRateFieldState();
                    }
                } else {
                    // Статус занимает ставку - активируем поле
                    updateRateFieldState();
                }
            });
        }

        // Инициализация
        toggleRateField();
        updateRateFieldState();
    });
</script>
@endpush