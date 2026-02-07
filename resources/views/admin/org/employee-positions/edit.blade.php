@extends('layouts.main')

@section('header-title')
    Обновление назначения должности сотруднику
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-4xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('employee-positions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку назначений
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Обновление назначения должности сотруднику</h1>
            <p class="text-[#565A5B] mt-1">Редактирование назначений для: "{{ $employee->person->last_name ?? '' }}
                {{ $employee->person->first_name ?? '' }}"</p>
        </div>

        <!-- Назначения должностей -->
        @foreach ($employee->positions as $position)
            <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden mb-6">
                <div class="p-6 md:p-8">
                    <h4 class="font-semibold text-[#565A5B] mb-4">Должность</h4>

                    <!-- Информация о текущей должности -->
                    <div class="bg-white/50 rounded-lg p-4 mb-4 border border-[#BFBFBF]">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#565A5B]">Название</span>
                                <span class="text-[#060606]">{{ $position->position->name ?? '' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#565A5B]">Ставка</span>
                                <span class="text-[#060606]">{{ $position->rate ?? '' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#565A5B]">Комиссариат</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->commissariat->name ?? '' }}</span>
                            </div>

                            @if ($position->department)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Отдел</span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->department->name ?? '' }}</span>
                                </div>
                            @endif
                            @if ($position->division)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Отделение</span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->division->name ?? '' }}</span>
                                </div>
                            @endif

                            @if ($position->is_independent !== false)
                                <i
                                    style="color: rgb(17, 183, 17)">({{ $position->is_independent ? 'Самостоятельная должность' : '' }})</i>
                            @endif
                        </div>
                    </div>

                    <!-- Форма обновления -->
                    <form action="{{ route('employee-positions.update', $position->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')


                        <input type="hidden" name="backUrl" value="{{ $backUrl }}">
                        <input type="hidden" name="employeeId" value="{{ $employeeId }}">

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
                                value="{{ old('position_id', $position->position->id ? trim($position->position->name) : '') }}">

                            {{-- Скрытое поле --}}
                            <input type="hidden" name="position_id" id="position_id"
                                value="{{ old('position_id', $position->position->id) }}">

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
                                        <li class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                            data-id="{{ $pos->id }}" data-name="{{ $pos->name }}">
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
                                    <option value="{{ $rate }}"
                                        {{ old('rate', $rate) == $position->rate ? 'selected' : '' }}>
                                        {{ $rate }}
                                    </option>
                                @endforeach
                            </select>
                        </div>




                        <div class="position-form space-y-6">

                            {{-- комиссариат --}}
                            <div class="relative">
                                <label class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Комиссариат *
                                </label>

                                <input type="text"
                                    class="commissariat-search w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                      focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none"
                                    placeholder="Выберите комиссариат" autocomplete="off"
                                    value="{{ old('commissariat_id', $position->commissariat->id ? $position->commissariat->name : '') }}">

                                <input type="hidden" name="commissariat_id" class="commissariat-id"
                                    value="{{ old('commissariat_id', $position->commissariat->id) }}">

                                <ul
                                    class="commissariat-list absolute z-10 mt-1 w-full bg-white border border-[#BFBFBF]
                   rounded-lg max-h-72 overflow-auto hidden">

                                    <li class="px-4 py-2 cursor-pointer text-red-500 hover:bg-gray-100" data-static="true"
                                        data-id="" data-name="">
                                        Очистить
                                    </li>

                                    @foreach ($commissariats as $commissariat)
                                        <li class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                            data-id="{{ $commissariat->id }}" data-name="{{ $commissariat->name }}">
                                            {{ $commissariat->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- отдел --}}
                            <div class="relative">
                                <label class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Отдел
                                </label>

                                <input type="text"
                                    class="department-search w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                      focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none"
                                    placeholder="Выберите отдел" autocomplete="off"
                                    value="{{ old('department_id', $position?->department?->id ? $position?->department?->name : '') }}">

                                <input type="hidden" name="department_id" class="department-id"
                                    value="{{ old('department_id', $position?->department?->id) }}">

                                <ul
                                    class="department-list absolute z-10 mt-1 w-full bg-white border border-[#BFBFBF]
                   rounded-lg max-h-72 overflow-auto hidden">

                                    <li class="px-4 py-2 cursor-pointer text-red-500 hover:bg-gray-100" data-static="true"
                                        data-id="" data-name="">
                                        Не выбирать
                                    </li>

                                    @foreach ($departments as $department)
                                        <li class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                            data-id="{{ $department->id }}" data-name="{{ $department->name }}"
                                            data-commissariat-id="{{ $department->commissariat->id }}"
                                            data-commissariat-name="{{ $department->commissariat->name }}">
                                            {{ $department->name }}
                                            <span class="text-gray-400">({{ $department->commissariat->name }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- отделение --}}
                            <div class="relative">
                                <label class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Отделение
                                </label>

                                <input type="text"
                                    class="division-search w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                      focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none"
                                    placeholder="Выберите отделение" autocomplete="off"
                                    value="{{ old('division_id', $position?->division?->id ? $position?->division?->name : '') }}">

                                <input type="hidden" name="division_id" class="division-id"
                                    value="{{ old('division_id', $position?->division?->id) }}">

                                <ul
                                    class="division-list
                                    absolute z-10 mt-1 w-full bg-white border border-[#BFBFBF] rounded-lg max-h-72
                                    overflow-auto hidden">

                                <li class="px-4 py-2 cursor-pointer text-red-500 hover:bg-gray-100" data-static="true"
                                    data-id="" data-name="">
                                    Не выбирать
                                </li>

                                @foreach ($divisions as $division)
                                    <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $division->id }}"
                                        data-name="{{ $division->name }}"
                                        data-department-id="{{ $division?->department?->id }}"
                                        data-department-name="{{ $division?->department?->name }}"
                                        data-commissariat-id="{{ $division->commissariat->id }}"
                                        data-commissariat-name="{{ $division->commissariat->name }}">
                                        {{ $division->name }}
                                    </li>
                                @endforeach
                                </ul>
                            </div>

                        </div>




                        <!-- Самостоятельная должность -->
                        <div>
                            <label for="is_independent" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Самостоятельная должность
                            </label>
                            <select name="is_independent" id="is_independent"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                <option value="0" {{ $position->is_independent ? '' : 'selected' }}>Нет</option>
                                <option value="1" {{ $position->is_independent ? 'selected' : '' }}>Да</option>
                            </select>
                        </div>



                        <div class="flex items-center justify-between pt-4 border-t border-[#BFBFBF]">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Обновить
                            </button>
                        </div>
                    </form>

                    <form
                        action="{{ route('employee-positions.delete', [
                            'id' => $position->id,
                            'back_url' => $backUrl,
                        ]) }}"
                        method="POST" class="mt-0.5 inline-block"
                        onsubmit="return confirm('Вы уверены, что хотите удалить это назначение?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection

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

        document.querySelectorAll('.position-form').forEach(form => {

            const commissariatInput = form.querySelector('.commissariat-search');
            const commissariatHidden = form.querySelector('.commissariat-id');
            const commissariatList = form.querySelector('.commissariat-list');

            const departmentInput = form.querySelector('.department-search');
            const departmentHidden = form.querySelector('.department-id');
            const departmentList = form.querySelector('.department-list');

            const divisionInput = form.querySelector('.division-search');
            const divisionHidden = form.querySelector('.division-id');
            const divisionList = form.querySelector('.division-list');

            // ---------- фильтр ----------
            function filterList(input, list) {
                const q = input.value.toLowerCase().trim();
                let visible = false;

                list.querySelectorAll('li').forEach(li => {
                    if (li.dataset.static === 'true') {
                        li.classList.remove('hidden');
                        visible = true;
                        return;
                    }

                    const name = (li.dataset.name || '').toLowerCase();
                    const show = !q || name.includes(q);

                    li.classList.toggle('hidden', !show);
                    if (show) visible = true;
                });

                list.classList.toggle('hidden', !visible);
            }

            // ---------- комиссариат ----------
            commissariatInput.addEventListener('focus', () => filterList(commissariatInput,
                commissariatList));
            commissariatInput.addEventListener('input', () => {
                commissariatHidden.value = '';
                departmentInput.value = '';
                departmentHidden.value = '';
                divisionInput.value = '';
                divisionHidden.value = '';
                filterList(commissariatInput, commissariatList);
            });

            commissariatList.querySelectorAll('li').forEach(li => {
                li.addEventListener('click', () => {
                    commissariatInput.value = li.dataset.name || '';
                    commissariatHidden.value = li.dataset.id || '';
                    commissariatList.classList.add('hidden');
                });
            });

            // ---------- отдел ----------
            departmentInput.addEventListener('focus', () => filterList(departmentInput,
                departmentList));
            departmentInput.addEventListener('input', () => {
                departmentHidden.value = '';
                divisionInput.value = '';
                divisionHidden.value = '';
                filterList(departmentInput, departmentList);
            });

            departmentList.querySelectorAll('li').forEach(li => {
                li.addEventListener('click', () => {
                    departmentInput.value = li.dataset.name || '';
                    departmentHidden.value = li.dataset.id || '';

                    commissariatInput.value = li.dataset.commissariatName || '';
                    commissariatHidden.value = li.dataset.commissariatId || '';

                    departmentList.classList.add('hidden');
                });
            });

            // ---------- отделение ----------
            divisionInput.addEventListener('focus', () => filterList(divisionInput, divisionList));
            divisionInput.addEventListener('input', () => {
                divisionHidden.value = '';
                filterList(divisionInput, divisionList);
            });

            divisionList.querySelectorAll('li').forEach(li => {
                li.addEventListener('click', () => {
                    divisionInput.value = li.dataset.name || '';
                    divisionHidden.value = li.dataset.id || '';

                    commissariatInput.value = li.dataset.commissariatName || '';
                    commissariatHidden.value = li.dataset.commissariatId || '';

                    if (li.dataset.departmentId) {
                        departmentInput.value = li.dataset.departmentName;
                        departmentHidden.value = li.dataset.departmentId;
                    } else {
                        departmentInput.value = '';
                        departmentHidden.value = '';
                    }

                    divisionList.classList.add('hidden');
                });
            });

            // ---------- закрытие ----------
            document.addEventListener('click', e => {
                if (!form.contains(e.target)) {
                    commissariatList.classList.add('hidden');
                    departmentList.classList.add('hidden');
                    divisionList.classList.add('hidden');
                }
            });

        });
    });
</script>
