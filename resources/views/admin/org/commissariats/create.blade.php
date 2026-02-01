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
                <a href="{{ route('commissariats.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
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
                        <input type="text" id="chief_employee_search" placeholder="Начните вводить ФИО"
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
                                data-name="Не назначать">
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
                                        {{ $employee->person->last_name ?? '*' }}
                                        {{ $employee->person->first_name ?? '*' }}
                                        {{ $employee->person->patronymic ?? '*' }}
                                        <span class="text-gray-400">(ID: {{ $employee->id ?? '*' }})</span>
                                    @else
                                        <span class="text-gray-400">Без ФИО (ID: {{ $employee->id }})</span>
                                    @endif
                                </li>
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

        function filterList(value) {
            const query = value.toLowerCase().trim();
            let hasVisible = false;

            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const id = item.dataset.id;

                if (query === '') {
                    item.classList.remove('hidden');
                    hasVisible = true;
                    return;
                }

                if (
                    id.includes(query) ||
                    (name && name.includes(query))
                ) {
                    item.classList.remove('hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            list.classList.toggle('hidden', !hasVisible);
        }

        input.addEventListener('focus', () => filterList(input.value));
        input.addEventListener('input', () => {
            hiddenInput.value = '';
            filterList(input.value);
        });

        items.forEach(item => {
            item.addEventListener('click', () => {
                input.value = item.dataset.name || `ID ${item.dataset.id}`;
                hiddenInput.value = item.dataset.id;
                list.classList.add('hidden');
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                list.classList.add('hidden');
            }
        });
    });
</script>
