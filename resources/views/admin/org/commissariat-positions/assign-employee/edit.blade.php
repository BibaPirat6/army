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
                    method="POST" class="space-y-6" id="edit-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="back_url" value="{{ $backUrl }}">
                    <!-- Скрытое поле для ставки, которое всегда отправляется -->
                    <input type="hidden" name="rate" id="rate-hidden" value="{{ old('rate', $employeePosition->rate) }}">

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
                            value="{{ old('chief_employee_name', $employeePosition->employee->getFullNameAttribute()) }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none">

                        <input type="hidden" name="chief_employee_id" id="chief_employee_id" 
                            value="{{ old('chief_employee_id', $employeePosition->employee_id) }}">

                        <ul id="chief_employee_list" class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF]
                               rounded-lg max-h-72 overflow-auto hidden shadow-lg">

                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать">
                                Очистить
                            </li>

                            @foreach ($employees as $emp)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" 
                                    data-id="{{ $emp->id }}"
                                    data-name="{{ trim($emp->getFullNameAttribute()) }}"
                                    {{ $emp->id == $employeePosition->employee_id ? 'style=background-color:#f0f0f0' : '' }}>
                                    {{ $emp->getFullNameAttribute() }}
                                    <span class="text-gray-400">(ID: {{ $emp->id }})</span>
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
                            Ставка <span class="text-red-500" id="rate-required">*</span>
                        </label>
                        <input type="number" 
                            id="rate-visible"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]" 
                            autocomplete="off"
                            placeholder="Введите ставку" 
                            value="{{ old('rate', $employeePosition->rate) }}" 
                            min="0.25" 
                            max="{{ $maxRateForInput }}" 
                            step="0.25"
                        >
                        @error('rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Минимум: 0.25, Максимум: {{ number_format($maxRateForInput, 2) }}, шаг: 0.25
                        </p>
                        <p class="mt-1 text-xs text-orange-600 hidden" id="rate-frozen-message">
                            ⚠️ Ставка заморожена. При данном статусе ставка сохраняется, но не занимает место.
                        </p>
                    </div>

                    <!-- Статус сотрудника на должности -->
                    <div>
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Статус <span class="text-red-500">*</span>
                        </label>
                        <select name="employee_position_status_id" id="status-select" required
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
        const rateVisible = document.getElementById('rate-visible');
        const rateHidden = document.getElementById('rate-hidden');
        const rateRequired = document.getElementById('rate-required');
        const rateFrozenMessage = document.getElementById('rate-frozen-message');
        const statusSelect = document.getElementById('status-select');
        const form = document.getElementById('edit-form');
        
        // Сохраняем оригинальные значения
        const originalRate = parseFloat(rateVisible?.value || 0);
        const originalStatusId = statusSelect?.value;
        const originalEmployeeId = hidden.value;
        
        // Получаем максимальную ставку
        let maxRate = parseFloat(rateVisible?.getAttribute('max') || 0);
        
        // Переменная для хранения ставки
        let savedRateValue = originalRate;

        // Функция обновления состояния поля ставки
        function updateRateFieldState() {
            const selectedOption = statusSelect?.options[statusSelect.selectedIndex];
            const occupiesRate = selectedOption?.dataset.occupiesRate === '1';
            
            if (!rateVisible || !rateHidden || !rateRequired) return;
            
            if (occupiesRate) {
                // Статус ЗАНИМАЕТ ставку - поле активно
                rateVisible.disabled = false;
                rateVisible.readOnly = false;
                rateVisible.classList.remove('bg-gray-100', 'cursor-not-allowed');
                rateRequired.style.display = 'inline';
                rateFrozenMessage.classList.add('hidden');
                
                // Восстанавливаем сохраненное значение
                if (savedRateValue > 0) {
                    rateVisible.value = savedRateValue;
                } else if (!rateVisible.value || parseFloat(rateVisible.value) === 0) {
                    rateVisible.value = 0.25;
                    savedRateValue = 0.25;
                }
                
                rateVisible.max = maxRate;
                
                // Синхронизируем скрытое поле
                rateHidden.value = rateVisible.value;
                
                // Проверяем границы
                validateRateValue();
            } else {
                // Статус НЕ занимает ставку - поле заблокировано, но значение сохраняется
                // Сохраняем текущее значение перед блокировкой
                if (rateVisible.value && parseFloat(rateVisible.value) > 0) {
                    savedRateValue = parseFloat(rateVisible.value);
                }
                
                rateVisible.disabled = true;
                rateVisible.readOnly = true;
                rateVisible.classList.add('bg-gray-100', 'cursor-not-allowed');
                rateRequired.style.display = 'none';
                rateFrozenMessage.classList.remove('hidden');
                
                // Показываем сохраненное значение
                rateVisible.value = savedRateValue > 0 ? savedRateValue : originalRate;
                
                // ВАЖНО: скрытое поле ВСЕГДА содержит актуальное значение
                rateHidden.value = savedRateValue > 0 ? savedRateValue : originalRate;
            }
        }

        function validateRateValue() {
            if (!rateVisible || rateVisible.disabled) return;
            
            let value = parseFloat(rateVisible.value);
            if (isNaN(value)) value = 0.25;
            if (value < 0.25) {
                rateVisible.value = 0.25;
                value = 0.25;
            }
            if (value > maxRate) {
                rateVisible.value = maxRate;
                value = maxRate;
                alert('Максимальная доступная ставка: ' + maxRate.toFixed(2));
            }
            
            savedRateValue = value;
            rateHidden.value = value;
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
        });

        items.forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.id || '';
                const name = item.dataset.name || '';
                input.value = name;
                hidden.value = id;
                close();
                updateRateFieldState();
            });
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                close();
            }
        });

        // Синхронизация видимого и скрытого поля ставки
        if (rateVisible) {
            rateVisible.addEventListener('change', validateRateValue);
            rateVisible.addEventListener('input', function() {
                if (!this.disabled) {
                    let value = parseFloat(this.value);
                    if (!isNaN(value) && value > 0) {
                        savedRateValue = value;
                        rateHidden.value = value;
                    }
                }
            });
        }

        // Обработчик изменения статуса
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const occupiesRate = selectedOption?.dataset.occupiesRate === '1';
                
                if (!occupiesRate) {
                    // Показываем предупреждение
                    const message = 'Выбранный статус НЕ занимает ставку.\n\n' +
                        'Текущая ставка (' + (savedRateValue > 0 ? savedRateValue : originalRate) + ') будет сохранена, ' +
                        'но не будет учитываться при расчете занятых ставок.\n\nПродолжить?';
                    
                    if (confirm(message)) {
                        updateRateFieldState();
                    } else {
                        // Возвращаем предыдущий статус
                        this.value = originalStatusId;
                        updateRateFieldState();
                    }
                } else {
                    updateRateFieldState();
                }
            });
        }

        // Перед отправкой формы убеждаемся, что скрытое поле содержит актуальное значение
        form.addEventListener('submit', function(e) {
            if (rateVisible.disabled) {
                // Если поле заблокировано, используем сохраненное значение
                rateHidden.value = savedRateValue > 0 ? savedRateValue : originalRate;
            } else {
                // Если поле активно, синхронизируем
                rateHidden.value = rateVisible.value;
            }
        });

        // Инициализация
        updateRateFieldState();
    });
</script>
@endpush