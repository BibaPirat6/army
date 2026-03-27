@extends('layouts.main')

@section('header-title')
    Создание назначения должности сотруднику
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('employee-positions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Назначение новой должности</h1>
            <p class="text-[#565A5B] mt-1">Назначение должности сотруднику: "{{ $employee->person->last_name ?? '' }}
                {{ $employee->person->first_name ?? '' }}"</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('employee-positions.store', $employee->id) }}" method="POST" class="space-y-6">
                    @csrf

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
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>


                    {{--  --}}





                    <!-- Ставка -->
                    <div>
                        <label for="rate" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Ставка *
                        </label>
                        <select name="rate" id="rate"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]"
                            required>
                            <option value="0.25">0.25</option>
                            <option value="0.5">0.5</option>
                            <option value="0.75">0.75</option>
                            <option value="1" selected>1</option>
                            <option value="1.25">1.25</option>
                            <option value="1.5">1.5</option>
                            <option value="1.75">1.75</option>
                            <option value="2">2</option>
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
                            autocomplete="off" value="{{ old('commissariat_id') }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="commissariat_id" id="commissariat_id"
                            value="{{ old('commissariat_id') }}">

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
                            autocomplete="off" value="{{ old('department_id') }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="department_id" id="department_id" value="{{ old('department_id') }}">

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
                                    data-name="{{ $department->name }}" data-commissariat-id="{{ $department->commissariat->id }}" data-commissariat-name="{{ $department->commissariat->name }}">
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
                            autocomplete="off"
                            value="{{ old('division_id') ? optional($divisions->firstWhere('id', old('division_id')))->name : '' }}">

                        {{-- скрытое поле для отправки формы --}}
                        <input type="hidden" name="division_id" id="division_id" value="{{ old('division_id') }}">

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
                                    data-name="{{$division->name}}" data-department-id="{{ $division?->department?->id }}" data-department-name="{{ $division?->department?->name }}" data-commissariat-id="{{ $division->commissariat->id }}" data-commissariat-name="{{ $division->commissariat->name }}">
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
                            <option value="0" selected>Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>






                    <!-- Кнопка отправки -->
                    <div class="pt-6 flex justify-end">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Назначить должность
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
            if (extra.clearFields) extra.clearFields.forEach(f => { f.value = ''; });
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
                clearFields: [departmentInput, departmentHidden, divisionInput, divisionHidden]
            });
            commissariatList.classList.add('hidden');
        });
    });

    // Отдел
    departmentList.querySelectorAll('li').forEach(item => {
        item.addEventListener('click', () => {
            const cleared = handleItemClick(item, departmentInput, departmentHidden, {
                setFields: [
                    { input: commissariatInput, hidden: commissariatHidden, name: item.dataset.commissariatName, id: item.dataset.commissariatId }
                ],
                clearFields: [divisionInput, divisionHidden]
            });
            departmentList.classList.add('hidden');
        });
    });

    // Отделение
    divisionList.querySelectorAll('li').forEach(item => {
        item.addEventListener('click', () => {
            const cleared = handleItemClick(item, divisionInput, divisionHidden, {
                setFields: [
                    { input: commissariatInput, hidden: commissariatHidden, name: item.dataset.commissariatName, id: item.dataset.commissariatId }
                ]
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
    [[commissariatInput, commissariatList], [departmentInput, departmentList], [divisionInput, divisionList]].forEach(([input, list]) => {
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
