@extends('layouts.main')

@section('header-title')
    Добавление отделения
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
            <h1 class="text-2xl font-bold text-[#060606]">Добавление отделения</h1>
            <p class="text-[#565A5B] mt-1">Заполните все необходимые поля для создания нового отделения</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('divisions.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    <!-- Название отделения -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название отделения *
                        </label>
                        <input type="text" name="name" id="name" placeholder="Название отделения"
                            value="{{ old('name') }}" required
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
                            value="{{ $department ? $department->name : "" }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="department_id" id="department_id2" value="{{ $department ? $department->id : "" }}">

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
                            value="{{ $commissariat ? $commissariat->name : "" }}">

                        {{-- hidden value --}}
                        <input type="hidden" name="commissariat_id" id="commissariat_id2"
                            value="{{ $commissariat ? $commissariat->id : "" }}">

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


                     {{-- начальник --}}
                    <div class="relative" id="chief-select">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Начальник
                        </label>

                        {{-- visible --}}
                        <input type="text" id="chief_employee_search" placeholder="Начните вводить ФИО" autocomplete="off"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                       focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none">

                        {{-- hidden --}}
                        <input type="hidden" name="chief_employee_id" id="chief_employee_id">

                        {{-- dropdown --}}
                        <ul id="chief_employee_list" class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF]
                       rounded-lg max-h-72 overflow-auto hidden shadow-lg">

                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать">
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
                    </div>



                    {{-- название должности начальника отдела --}}
                    <div class="relative mt-4">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Должность начальника отдела *
                        </label>

                        {{-- visible input --}}
                        <input type="text" id="chief_position_search" placeholder="Начните вводить название должности (например: ЗГТ)"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                        focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                        outline-none transition-colors text-[#060606]"
                            autocomplete="off"
                            value="{{ old('chief_position_name') ?? '' }}" required>

                        {{-- hidden value --}}
                        <input type="hidden" name="chief_position_id" id="chief_position_id"
                            value="{{ old('chief_position_id') ?? '' }}" required>

                        {{-- dropdown --}}
                        <ul id="chief_position_list"
                            class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF]
                                rounded-lg max-h-72 overflow-auto hidden">

                            {{-- опция "Не назначать" --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать" data-static="true">
                                Очистить
                            </li>

                            @foreach ($positions as $pos)
                                @php
                                    // Попытка получить читаемое имя должности и chiefType
                                    $posName = $pos->name ?? ($pos->position->name ?? '');
                                    $chiefTypeName = $pos->ChiefType->name ?? ($pos->position->ChiefType->name ?? null);
                                @endphp
                                @if ($chiefTypeName)
                                    <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $pos->id }}"
                                        data-name="{{ $posName }}">
                                        {{ $posName }}
                                        <span class="text-gray-400">(ID: {{ $pos->id }})</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
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
                            Создать отделение
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('chief-select');
        const input = container.querySelector('#chief_employee_search');
        const hidden = container.querySelector('#chief_employee_id');
        const list = container.querySelector('#chief_employee_list');
        const items = list.querySelectorAll('li');

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

                if (!q || name.includes(q) || id.includes(q)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // focus
        input.addEventListener('focus', () => {
            open();
            filter(input.value);
        });

        // typing
        input.addEventListener('input', () => {
            hidden.value = ''; // сбрасываем только при ручном вводе
            open();
            filter(input.value);
        });

        // select
        items.forEach(item => {
            item.addEventListener('click', () => {
                const id = item.dataset.id || '';
                const name = item.dataset.name || '';

                input.value = name;
                hidden.value = id;

                close();
            });
        });

        // click outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                close();
            }
        });
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




<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('chief_position_search');
        const hiddenInput = document.getElementById('chief_position_id');
        const list = document.getElementById('chief_position_list');
        if (!input || !list) return;

        const items = list.querySelectorAll('li');

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
                if (query === '' || name.includes(query) || id.includes(query)) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });
            list.classList.toggle('hidden', !hasVisible);
        }

        input.addEventListener('focus', () => { showList(); filterList(input.value); });
        input.addEventListener('input', () => { hiddenInput.value = ''; showList(); filterList(input.value); });

        items.forEach(item => {
            item.addEventListener('click', () => {
                if (item.dataset.static === 'true') {
                    const wasNotEmpty = input.value.trim() !== '' || hiddenInput.value !== '';
                    input.value = '';
                    hiddenInput.value = '';
                    if (wasNotEmpty) { showList(); filterList(''); } else { hideList(); }
                    return;
                }
                input.value = item.dataset.name || `ID ${item.dataset.id}`;
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
