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
                            Начальник
                        </label>

                        {{-- visible input --}}
                        <input required type="text" id="chief_employee_search" placeholder="Начните вводить ФИО"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
               outline-none transition-colors text-[#060606]"
                            autocomplete="off">

                        {{-- hidden value --}}
                        <input type="hidden" name="chief_employee_id" id="chief_employee_id"
                            value="{{ old('chief_employee_id') }}">

                        {{-- dropdown --}}
                        <ul id="chief_employee_list"
                            class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF]
               rounded-lg max-h-72 overflow-auto hidden">

                            {{-- опция "Не назначать" --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать" data-static="true">
                                Не назначать
                            </li>

                            @foreach ($employees as $employee)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $employee->id }}"
                                    data-name="{{ trim(
                                        ($employee->person?->last_name ?? '') .
                                            ' ' .
                                            ($employee->person?->first_name ?? '') .
                                            ' ' .
                                            ($employee->person?->patronymic ?? ''),
                                    ) }}"
                                    data-search="{{ $employee->id }}">
                                    @if ($employee->person)
                                        {{ $employee->getFullNameAttribute()}}
                                        <span class="text-gray-400">(ID: {{ $employee->id ?? '*' }})</span>
                                    @else
                                        <span class="text-gray-400">Без ФИО (ID: {{ $employee->id }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- должность начальника --}}
                    <div>
                        <label for="chief_position_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Должность начальника
                        </label>
                        <select required name="chief_position_id" id="chief_position_id"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            <option value="" selected disabled>{{ old('chief_position_id') ? '' : 'Выберите должность' }}</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}" {{ old('chief_position_id') == $position->id ? 'selected' : '' }}>
                                    {{ $position->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- общая ставка должности --}}
                     <div>
                        <label for="rate_total" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Общая ставка должности
                        </label>
                        <select required name="rate_total" id="rate_total"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
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

                    <!-- x -->
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по горизонтали
                        </label>
                        <input required type="number" name="longitude" id="longitude" placeholder="Ось х" max="200"
                            min="1" value="{{ old('longitude', $x) }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- y -->
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по вертикали
                        </label>
                        <input required type="number" name="latitude" id="latitude" placeholder="Ось y" max="120"
                            min="1" value="{{ old('latitude', $y) }}"
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

                    const wasNotEmpty =
                        input.value.trim() !== '' || hiddenInput.value !== '';

                    input.value = '';
                    hiddenInput.value = '';

                    if (wasNotEmpty) {
                        showList();
                        filterList('');
                    } else {
                        hideList();
                    }

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
