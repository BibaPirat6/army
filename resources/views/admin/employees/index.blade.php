@extends('layouts.main')

@section('header-title')
    Сотрудники
@endsection

<style>
    /* Стили для range input в разных браузерах */
    input[type="range"] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background: transparent;
        cursor: pointer;
        z-index: 10;
    }

    /* Стили для бегунка в WebKit браузерах */
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 16px;
        height: 16px;
        background: #A60644;
        border-radius: 50%;
        cursor: pointer;
        pointer-events: all;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        margin-top: -6px;
        /* Центрирование относительно трека */
    }

    input[type="range"]::-webkit-slider-runnable-track {
        width: 100%;
        height: 4px;
        background: transparent;
        border-radius: 2px;
    }

    /* Стили для бегунка в Firefox */
    input[type="range"]::-moz-range-thumb {
        width: 16px;
        height: 16px;
        background: #A60644;
        border-radius: 50%;
        cursor: pointer;
        pointer-events: all;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }

    input[type="range"]::-moz-range-track {
        width: 100%;
        height: 4px;
        background: transparent;
        border-radius: 2px;
    }

    /* Стили для бегунка в IE/Edge */
    input[type="range"]::-ms-thumb {
        width: 16px;
        height: 16px;
        background: #A60644;
        border-radius: 50%;
        cursor: pointer;
        pointer-events: all;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }

    input[type="range"]::-ms-track {
        width: 100%;
        height: 4px;
        background: transparent;
        border-color: transparent;
        color: transparent;
    }

    /* Z-index для предотвращения наложений */
    #rate_min {
        z-index: 20;
    }

    #rate_max {
        z-index: 30;
    }

    /* Убираем стандартные стили Firefox */
    input[type="range"]::-moz-focus-outer {
        border: 0;
    }

    input[type="range"]:focus::-moz-range-thumb {
        box-shadow: 0 0 0 2px rgba(166, 6, 68, 0.3), 0 1px 3px rgba(0, 0, 0, 0.4);
    }

    input[type="range"]:focus::-webkit-slider-thumb {
        box-shadow: 0 0 0 2px rgba(166, 6, 68, 0.3), 0 1px 3px rgba(0, 0, 0, 0.4);
    }
</style>

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
        <form method="GET" class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 mb-4" id="filterForm">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 mb-3">
                <!-- Поиск -->
                <div class="col-span-2 md:col-span-1 relative">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="search" name="search" value="{{ $filters->search }}"
                        placeholder="Поиск (ID, ФИО, логин, должность, комиссариат, отдел, отделение)..."
                        class="w-full pl-9 pr-3 py-2 text-sm border-gray-200 rounded-lg focus:ring-1 focus:ring-black focus:border-black outline-none transition">
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
                    <select id="department_id" name="department_id" class="tom-select w-full">
                        <option value="">Отдел</option>
                        @foreach ($departments as $item)
                            @php
                                $deptLabel = $item->name;
                                if (!$filters->commissariatId && $item->commissariat) {
                                    $deptLabel .= ' ← ' . $item->commissariat->name;
                                }
                            @endphp
                            <option value="{{ $item->id }}" @selected($filters->departmentId == $item->id)
                                data-commissariat="{{ $item->commissariat_id }}">
                                {{ $deptLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Отделение (зависит от комиссариата и отдела) -->
                <div>
                    <select id="division_id" name="division_id" class="tom-select w-full">
                        <option value="">Отделение</option>
                        @foreach ($divisions as $item)
                            @php
                                $divLabel = $item->name;
                                $parents = [];

                                if (!$filters->commissariatId && $item->commissariat) {
                                    $parents[] = $item->commissariat->name;
                                }
                                if (!$filters->departmentId && $item->department) {
                                    $parents[] = $item->department->name;
                                }

                                if (!empty($parents)) {
                                    $divLabel .= ' ← ' . implode(' → ', $parents);
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
                        <span class="text-[#565A5B] whitespace-nowrap">Ставка:</span>
                        <span id="rate_min_label"
                            class="font-semibold text-[#A60644]">{{ $filters->rateMin ?? 0.25 }}</span>
                        <span class="text-[#BFBFBF]">—</span>
                        <span id="rate_max_label" class="font-semibold text-[#A60644]">{{ $filters->rateMax ?? 2 }}</span>
                    </div>
                    <div class="relative mt-1" style="height: 24px;">
                        <!-- Фоновый трек -->
                        <div class="absolute top-1/2 -translate-y-1/2 w-full h-1.5 bg-gray-200 rounded-full"></div>
                        <!-- Активный трек -->
                        <div id="rate_range_track" class="absolute top-1/2 -translate-y-1/2 h-1.5 bg-[#A60644] rounded-full"
                            style="left: 0%; right: 0%;"></div>
                        <!-- Контейнер для ползунков -->
                        <div class="relative w-full h-full">
                            <input type="range" id="rate_min" name="rate_min" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMin ?? 0.25 }}"
                                class="absolute w-full h-full appearance-none bg-transparent pointer-events-none"
                                style="top: 0; left: 0; margin: 0;">
                            <input type="range" id="rate_max" name="rate_max" min="0.25" max="2"
                                step="0.25" value="{{ $filters->rateMax ?? 2 }}"
                                class="absolute w-full h-full appearance-none bg-transparent pointer-events-none"
                                style="top: 0; left: 0; margin: 0;">
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
                    class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-black transition shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Применить
                </button>
                <a href="{{ route('employees.index') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
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

        // Определяем браузер
        const isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;

        function updateRateRange() {
            let min = parseFloat(rateMin.value);
            let max = parseFloat(rateMax.value);

            // Предотвращаем пересечение
            if (min > max) {
                if (document.activeElement === rateMin) {
                    rateMax.value = min;
                    max = min;
                } else {
                    rateMin.value = max;
                    min = max;
                }
            }

            // Вычисляем проценты для трека
            const range = 2 - 0.25; // 1.75
            const minPercent = ((min - 0.25) / range) * 100;
            const maxPercent = ((max - 0.25) / range) * 100;

            // Обновляем позицию трека
            rateRangeTrack.style.left = minPercent + '%';
            rateRangeTrack.style.right = (100 - maxPercent) + '%';

            // Обновляем метки
            rateMinLabel.textContent = min.toFixed(2);
            rateMaxLabel.textContent = max.toFixed(2);
        }

        // Обработчики событий
        rateMin.addEventListener('input', updateRateRange);
        rateMax.addEventListener('input', updateRateRange);

        // Дополнительные обработчики для Firefox
        if (isFirefox) {
            rateMin.addEventListener('change', updateRateRange);
            rateMax.addEventListener('change', updateRateRange);
        }

        // Инициализация
        updateRateRange();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const commissariatSelect = document.getElementById('commissariat_id');
            const departmentSelect = document.getElementById('department_id');
            const divisionSelect = document.getElementById('division_id');

            // Кэшируем все опции с их данными
            const allDepartments = Array.from(departmentSelect.options).filter(opt => opt.value).map(opt => ({
                value: opt.value,
                text: opt.textContent.replace(/ ← .+$/, ''), // чистое название
                commissariat: opt.dataset.commissariat,
                commissariatName: opt.textContent.match(/← (.+)$/)?.[1] || ''
            }));

            const allDivisions = Array.from(divisionSelect.options).filter(opt => opt.value).map(opt => ({
                value: opt.value,
                text: opt.textContent.replace(/ ← .+$/, ''),
                commissariat: opt.dataset.commissariat,
                department: opt.dataset.department,
                parents: opt.textContent.match(/← (.+)$/)?.[1] || ''
            }));

            function updateDepartments(commissariatId) {
                const prevValue = departmentSelect.value;
                departmentSelect.innerHTML = '<option value="">Отдел</option>';

                allDepartments.forEach(dept => {
                    if (!commissariatId || dept.commissariat === commissariatId) {
                        const opt = new Option(
                            commissariatId ? dept.text : (dept.commissariatName ?
                                `${dept.text} ← ${dept.commissariatName}` : dept.text),
                            dept.value
                        );
                        opt.dataset.commissariat = dept.commissariat;
                        departmentSelect.add(opt);
                    }
                });

                departmentSelect.value = Array.from(departmentSelect.options).some(o => o.value === prevValue) ?
                    prevValue : '';
                if (departmentSelect.tomselect) departmentSelect.tomselect.sync();
                updateDivisions(commissariatId, departmentSelect.value);
            }

            function updateDivisions(commissariatId, departmentId) {
                const prevValue = divisionSelect.value;
                divisionSelect.innerHTML = '<option value="">Отделение</option>';

                allDivisions.forEach(div => {
                    let show = true;
                    if (commissariatId && div.commissariat && div.commissariat !== commissariatId) show =
                        false;
                    if (departmentId && div.department && div.department !== departmentId) show = false;

                    if (show) {
                        let label = div.text;
                        if (!commissariatId || !departmentId) {
                            const parts = [];
                            if (!commissariatId && div.commissariatName) parts.push(div.commissariatName);
                            if (!departmentId && div.departmentName) parts.push(div.departmentName);
                            if (parts.length) label += ' ← ' + parts.join(' → ');
                        }

                        const opt = new Option(label, div.value);
                        opt.dataset.commissariat = div.commissariat;
                        opt.dataset.department = div.department;
                        divisionSelect.add(opt);
                    }
                });

                divisionSelect.value = Array.from(divisionSelect.options).some(o => o.value === prevValue) ?
                    prevValue : '';
                if (divisionSelect.tomselect) divisionSelect.tomselect.sync();
            }

            commissariatSelect.addEventListener('change', () => updateDepartments(commissariatSelect.value));
            departmentSelect.addEventListener('change', () => updateDivisions(commissariatSelect.value,
                departmentSelect.value));

            // Инициализация
            if (commissariatSelect.value) updateDepartments(commissariatSelect.value);
        });
    </script>
@endpush
