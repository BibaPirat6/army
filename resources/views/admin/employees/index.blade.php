@extends('layouts.main')

@section('header-title')
    Сотрудники
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="w-full mx-auto p-4">
        <!-- кнопка создания -->
        <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-[#060606]">Сотрудники</h1>
                <p class="text-[#565A5B] text-sm">Список всех сотрудников системы</p>
            </div>

            <a href="{{ route('employees.create', [
                'back_url' => url()->full(),
            ]) }}"
                class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors shadow hover:shadow-md active:scale-[0.98]">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Создать сотрудника
            </a>
        </div>

        <!-- Фильтры -->
        <form method="GET" class="bg-white shadow-md rounded-xl p-4 mb-4" id="filterForm">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 mb-3">
                <!-- Поиск -->
                <div class="col-span-2 md:col-span-1">
                    <input type="text" id="search" name="search" value="{{ $filters->search }}"
                        placeholder="🔍 Поиск (ФИО, логин, должность)..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] transition outline-none">
                </div>

                <!-- Статус сотрудника -->
                <div>
                    <select id="employee_status" name="employee_status" class="tom-select w-full">
                        <option value="">Статус сотрудника</option>
                        <option value="working" @selected($filters->employeeStatus === 'working')>💼 Работает</option>
                        <option value="vacation" @selected($filters->employeeStatus === 'vacation')>🏖️ Отпуск</option>
                        <option value="maternity" @selected($filters->employeeStatus === 'maternity')>👶 Декрет</option>
                    </select>
                </div>

                <!-- Роль пользователя -->
                <div>
                    <select id="user_role" name="user_role" class="tom-select w-full">
                        <option value="">Роль пользователя</option>
                        <option value="admin" @selected($filters->userRole === 'admin')>👑 Администратор</option>
                        <option value="user" @selected($filters->userRole === 'user')>👤 Пользователь</option>
                    </select>
                </div>


                <!-- Комиссариат -->
                <div>
                    <select id="commissariat_id" name="commissariat_id" class="tom-select w-full">
                        <option value="">Комиссариат</option>
                        @foreach ($commissariats as $item)
                            <option value="{{ $item->id }}" @selected($filters->commissariatId == $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Отдел (зависит от комиссариата) -->
                <div>
                    <select id="department_id" name="department_id" class="tom-select w-full"
                        data-depends-on="commissariat">
                        <option value="">Отдел</option>
                        @foreach ($departments as $item)
                            @php
                                $deptLabel = $item->name;
                                if ($item->commissariat_id && !$filters->commissariatId) {
                                    $deptCommissariat = $commissariats->firstWhere('id', $item->commissariat_id);
                                    if ($deptCommissariat) {
                                        $deptLabel .= ' (' . $deptCommissariat->name . ')';
                                    }
                                }
                            @endphp
                            <option value="{{ $item->id }}" @selected($filters->departmentId == $item->id)
                                data-commissariat="{{ $item->commissariat_id }}">
                                {{ $deptLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Отделение (зависит от комиссариата ИЛИ отдела) -->
                <div>
                    <select id="division_id" name="division_id" class="tom-select w-full"
                        data-depends-on="commissariat,department">
                        <option value="">Отделение</option>
                        @foreach ($divisions as $item)
                            @php
                                $divLabel = $item->name;
                                $divInfo = [];

                                // Показываем зависимости только если не выбран комиссариат/отдел
                                if (!$filters->commissariatId && $item->commissariat_id) {
                                    $divCommissariat = $commissariats->firstWhere('id', $item->commissariat_id);
                                    if ($divCommissariat) {
                                        $divInfo[] = $divCommissariat->name;
                                    }
                                }

                                if (!$filters->departmentId && $item->department_id) {
                                    // Используем коллекцию departments, переданную из контроллера
                                    $divDepartment = $departments->firstWhere('id', $item->department_id);
                                    if ($divDepartment) {
                                        $divInfo[] = $divDepartment->name;
                                    }
                                }

                                if (!empty($divInfo)) {
                                    $divLabel .= ' (' . implode(' → ', $divInfo) . ')';
                                }
                            @endphp
                            <option value="{{ $item->id }}" @selected($filters->divisionId == $item->id)
                                data-commissariat="{{ $item->commissariat_id }}"
                                data-department="{{ $item->department_id }}">
                                {{ $divLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <!-- Ставка (range slider) -->
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-600 whitespace-nowrap">Ставка:</span>
                        <span id="rate_min_label"
                            class="font-semibold text-[#A60644]">{{ $filters->rateMin ?? 0.25 }}</span>
                        <span class="text-gray-400">—</span>
                        <span id="rate_max_label" class="font-semibold text-[#A60644]">{{ $filters->rateMax ?? 2 }}</span>
                    </div>
                    <div class="relative mt-1 px-0.5">
                        <div class="relative h-1.5">
                            <div class="absolute inset-0 bg-gray-200 rounded-full"></div>
                            <div id="rate_range_track" class="absolute inset-y-0 bg-[#A60644]/60 rounded-full"
                                style="left: 0%; right: 0%;"></div>
                            <input type="range" id="rate_min" name="rate_min" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMin ?? 0.25 }}"
                                class="absolute inset-y-0 w-full appearance-none bg-transparent pointer-events-none z-20
                    [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none 
                    [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 
                    [&::-webkit-slider-thumb]:bg-[#A60644] [&::-webkit-slider-thumb]:rounded-full 
                    [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-pointer">
                            <input type="range" id="rate_max" name="rate_max" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMax ?? 2 }}"
                                class="absolute inset-y-0 w-full appearance-none bg-transparent pointer-events-none z-30
                    [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none 
                    [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4 
                    [&::-webkit-slider-thumb]:bg-[#A60644] [&::-webkit-slider-thumb]:rounded-full 
                    [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:cursor-pointer">
                        </div>
                    </div>
                </div>

                <!-- Сортировка -->
                <div>
                    <select id="sort_by" name="sort_by" class="tom-select w-full">
                        <option value="id" @selected($filters->sortBy === 'id')>Сортировать по</option>
                        <option value="full_name" @selected($filters->sortBy === 'full_name')>ФИО</option>
                        <option value="rate_total" @selected($filters->sortBy === 'rate_total')>Общей ставке</option>
                        <option value="occupied_rate" @selected($filters->sortBy === 'occupied_rate')>Занятым ставкам</option>
                        <option value="available_rate" @selected($filters->sortBy === 'available_rate')>Свободным ставкам</option>
                        <option value="user_role" @selected($filters->sortBy === 'user_role')>Роли пользователя</option>
                    </select>
                </div>

                <!-- Направление сортировки -->
                <div>
                    <select id="sort_direction" name="sort_direction" class="tom-select w-full">
                        <option value="desc" @selected($filters->sortDirection === 'desc')>↓ По убыванию</option>
                        <option value="asc" @selected($filters->sortDirection === 'asc')>↑ По возрастанию</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-[#A60644] hover:bg-[#A60644]/80 text-white text-sm font-medium rounded-lg transition shadow-sm hover:shadow focus:ring-2 focus:ring-offset-2 focus:ring-[#A60644]">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Применить
                </button>
                <a href="{{ route('employees.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition focus:ring-2 focus:ring-offset-2 focus:ring-[#A60644]">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Сбросить
                </a>
            </div>
        </form>


        {{-- таблица --}}
        <div class="rounded-lg border border-[#BFBFBF]">
            <table class="min-w-full divide-y divide-[#BFBFBF] bg-[#e7e1e1] text-sm">
                {{-- шапка таблицы --}}
                <thead class="bg-[#d5cfcf]">
                    <tr>
                        <form method="GET" action="{{ route('employees.index') }}">

                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">ID/статус</th>

                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Пользователь</th>
                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Персона (ФИО)
                            </th>

                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Должности
                            </th>

                            <th class="px-4 py-2 text-right text-[#060606] font-medium whitespace-nowrap">Действия</th>
                        </form>
                    </tr>
                </thead>

                @include('admin.employees.partials.table-body')
            </table>
        </div>

        @include('includes.pagination', ['paginator' => $employees])
    </div>
@endsection


@push('scripts')
    <script>
        // Range slider для ставок
        const rateMin = document.getElementById('rate_min');
        const rateMax = document.getElementById('rate_max');
        const rateMinLabel = document.getElementById('rate_min_label');
        const rateMaxLabel = document.getElementById('rate_max_label');
        const rateRangeTrack = document.getElementById('rate_range_track');

        function updateRateRange() {
            const min = parseFloat(rateMin.value);
            const max = parseFloat(rateMax.value);

            if (min > max) {
                if (this === rateMin) {
                    rateMax.value = min;
                } else {
                    rateMin.value = max;
                }
            }

            const finalMin = parseFloat(rateMin.value);
            const finalMax = parseFloat(rateMax.value);

            const minPercent = ((finalMin - 0.25) / (2 - 0.25)) * 100;
            const maxPercent = ((finalMax - 0.25) / (2 - 0.25)) * 100;

            rateRangeTrack.style.left = minPercent + '%';
            rateRangeTrack.style.right = (100 - maxPercent) + '%';

            rateMinLabel.textContent = finalMin;
            rateMaxLabel.textContent = finalMax;
        }

        rateMin.addEventListener('input', updateRateRange);
        rateMax.addEventListener('input', updateRateRange);
        updateRateRange();

        // Каскадные фильтры с AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const commissariatSelect = document.getElementById('commissariat_id');
            const departmentSelect = document.getElementById('department_id');
            const divisionSelect = document.getElementById('division_id');

            let isUpdating = false;

            function updateSelectWithData(selectElement, options, selectedValue = null, placeholder = '') {
                // Сохраняем TomSelect инстанс если есть
                const tomSelectInstance = selectElement.tomselect;

                // Очищаем select
                selectElement.innerHTML = '';

                // Добавляем placeholder
                const defaultOption = new Option(placeholder || selectElement.getAttribute('data-placeholder') ||
                    'Выберите...', '');
                selectElement.add(defaultOption);

                // Добавляем опции
                options.forEach(option => {
                    const opt = new Option(option.text, option.value);
                    if (option.data) {
                        Object.keys(option.data).forEach(key => {
                            opt.setAttribute(`data-${key}`, option.data[key]);
                        });
                    }
                    selectElement.add(opt);
                });

                // Устанавливаем выбранное значение
                if (selectedValue && options.some(opt => opt.value == selectedValue)) {
                    selectElement.value = selectedValue;
                }

                // Обновляем TomSelect
                if (tomSelectInstance) {
                    tomSelectInstance.sync();
                }
            }

            async function loadFilterOptions(commissariatId, departmentId = null) {
                if (isUpdating) return;
                isUpdating = true;

                try {
                    // Сохраняем текущие значения
                    const currentDepartmentValue = departmentSelect.value;
                    const currentDivisionValue = divisionSelect.value;

                    // Если комиссариат не выбран, загружаем все отделы и отделения
                    if (!commissariatId) {
                        const response = await fetch(`/api/filter-options`);
                        const data = await response.json();

                        updateSelectWithData(departmentSelect,
                            data.departments.map(d => ({
                                value: d.id,
                                text: d.name,
                                data: {
                                    commissariat: d.commissariat_id
                                }
                            })),
                            currentDepartmentValue,
                            'Отдел'
                        );

                        updateSelectWithData(divisionSelect,
                            data.divisions.map(d => ({
                                value: d.id,
                                text: d.name,
                                data: {
                                    commissariat: d.commissariat_id,
                                    department: d.department_id
                                }
                            })),
                            currentDivisionValue,
                            'Отделение'
                        );
                    } else {
                        // Загружаем данные для выбранного комиссариата
                        const params = new URLSearchParams({
                            commissariat_id: commissariatId
                        });
                        if (departmentId) {
                            params.append('department_id', departmentId);
                        }

                        const response = await fetch(`/api/filter-options?${params}`);
                        const data = await response.json();

                        // Обновляем отделы
                        updateSelectWithData(departmentSelect,
                            data.departments.map(d => ({
                                value: d.id,
                                text: d.name,
                                data: {
                                    commissariat: d.commissariat_id
                                }
                            })),
                            currentDepartmentValue,
                            'Отдел'
                        );

                        // Обновляем отделения
                        updateSelectWithData(divisionSelect,
                            data.divisions.map(d => ({
                                value: d.id,
                                text: d.name,
                                data: {
                                    commissariat: d.commissariat_id,
                                    department: d.department_id
                                }
                            })),
                            currentDivisionValue,
                            'Отделение'
                        );
                    }
                } catch (error) {
                    console.error('Error loading filter options:', error);
                } finally {
                    isUpdating = false;
                }
            }

            // Обработчик изменения комиссариата
            commissariatSelect.addEventListener('change', function() {
                const commissariatId = this.value;
                loadFilterOptions(commissariatId);
            });

            // Обработчик изменения отдела
            departmentSelect.addEventListener('change', function() {
                const commissariatId = commissariatSelect.value;
                const departmentId = this.value;

                if (commissariatId) {
                    loadFilterOptions(commissariatId, departmentId);
                }
            });

            // Инициализация при загрузке
            const initialCommissariatId = commissariatSelect.value;
            if (initialCommissariatId) {
                loadFilterOptions(initialCommissariatId);
            }
        });
    </script>
@endpush
