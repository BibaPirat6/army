@extends('layouts.main')

@section('header-title')
    Назначение сотрудника на штатную должность
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
            <h1 class="text-2xl font-bold text-[#060606]">Назначение сотрудника</h1>
            <p class="text-[#565A5B] mt-1">
                Должность: <span class="font-semibold text-[#A60644]">{{ $commissariatPosition->position->name }}</span>
            </p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('commissariat-positions.assign.store', [
                'id' => $commissariatPosition->id,
                ]) }}"
                    method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="back_url" value="{{ $backUrl }}">

                    <!-- Информация о свободных ставках -->
                    @php
                        $occupiedRate = $commissariatPosition->employeePositions()
                            ->whereHas('employeePositionStatus', function($query) {
                                $query->where('occupies_rate', true);
                            })
                            ->sum('rate');
                        $availableRate = $commissariatPosition->rate_total - $occupiedRate;
                    @endphp
                    
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
                                    Максимальная ставка для назначения: {{ number_format($availableRate, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Сотрудник --}}
                    <div class="relative" id="chief-select">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Сотрудник <span class="text-red-500">*</span>
                        </label>

                        {{-- visible --}}
                        <input type="text" id="chief_employee_search" 
                            placeholder="Начните вводить ФИО" 
                            autocomplete="off"
                            value="{{ $employee ? trim($employee->getFullNameAttribute()) : old('chief_employee_name', '') }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none">

                        {{-- hidden --}}
                        <input type="hidden" name="chief_employee_id" id="chief_employee_id" 
                            value="{{ $employee ? $employee->id : old('chief_employee_id', '') }}">

                        {{-- dropdown --}}
                        <ul id="chief_employee_list" class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF] rounded-lg max-h-72 overflow-auto hidden shadow-lg">
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id="" data-name="" data-static="true">
                                Очистить
                            </li>
                            @foreach ($employees as $emp)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 {{ $employee && $employee->id == $emp->id ? 'bg-gray-100' : '' }}" 
                                    data-id="{{ $emp->id }}"
                                    data-name="{{ trim($emp->getFullNameAttribute()) }}">
                                    {{ $emp->getFullNameAttribute() }}
                                    <span class="text-gray-400">(ID: {{ $emp->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Ставка -->
                    <div>
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Ставка <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]" 
                            autocomplete="off"
                            placeholder="Введите ставку" 
                            value="{{ old('rate', min($availableRate, 1.00)) }}" 
                            min="0.25" 
                            max="{{ $availableRate }}" 
                            step="0.25" 
                            name="rate"
                            required>
                        @error('rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Минимум: 0.25, Максимум: {{ number_format($availableRate, 2) }}, шаг: 0.25
                            @if($availableRate < 0.25)
                                <span class="text-red-500 block">Внимание! Доступно менее 0.25 ставки. Назначение невозможно.</span>
                            @endif
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
                                    {{ old('employee_position_status_id', 1) == $status->id ? 'selected' : '' }}
                                    style="border-left-color: {{ $status->color }}; border-left-width: 3px;">
                                    {{ $status->name }}
                                    @if(!$status->occupies_rate)
                                        (не занимает ставку)
                                    @endif
                                </option>
                            @endforeach
                        </select>
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
                            Назначить сотрудника
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
        const rateInput = document.querySelector('input[name="rate"]');
        const statusSelect = document.querySelector('select[name="employee_position_status_id"]');
        const maxRate = {{ $availableRate }};
        
        // Валидация ставки при изменении
        if (rateInput) {
            rateInput.addEventListener('change', function() {
                let value = parseFloat(this.value);
                if (isNaN(value)) value = 0.25;
                if (value < 0.25) this.value = 0.25;
                if (value > maxRate) {
                    this.value = maxRate;
                    alert('Максимальная доступная ставка: ' + maxRate);
                }
            });
        }
        
        // Показываем информацию о статусе
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const occupiesRate = selectedOption.dataset.occupiesRate === '1';
                
                if (!occupiesRate && rateInput) {
                    console.log('Выбран статус, который не занимает ставку');
                }
            });
        }
    });
</script>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('chief-select');
        const input = container.querySelector('#chief_employee_search');
        const hidden = container.querySelector('#chief_employee_id');
        const list = container.querySelector('#chief_employee_list');
        const items = list.querySelectorAll('li');
        const form = document.querySelector('form');

        function openList() {
            list.classList.remove('hidden');
        }

        function closeList() {
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
                const name = (item.dataset.name || '').toLowerCase();
                const id = item.dataset.id || '';
                
                if (!query || name.includes(query) || id.includes(query)) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            list.classList.toggle('hidden', !hasVisible);
        }


        input.addEventListener('focus', () => {
            if (!input.readOnly) {
                filterList(input.value);
                openList();
            }
        });

        input.addEventListener('input', () => {
            if (!input.readOnly) {
                hidden.value = '';
                filterList(input.value);
                openList();
            }
        });

        items.forEach(item => {
            item.addEventListener('click', () => {
                if (item.dataset.static === 'true') {
                    input.value = '';
                    hidden.value = '';
                    input.removeAttribute('readonly');
                } else {
                    input.value = item.dataset.name;
                    hidden.value = item.dataset.id;
                    input.setAttribute('readonly', true);
                }
                closeList();
            });
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                closeList();
            }
        });
    });
</script>