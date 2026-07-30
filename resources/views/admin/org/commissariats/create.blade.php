{{-- ============ ФОРМА СОЗДАНИЯ КОМИССАРИАТА ============ --}}
@extends('layouts.main')

@section('header-title')
    Добавление коммиссариата
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
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

        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('commissariats.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    {{-- Название --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название комиссариата *
                        </label>
                        <input type="text" name="name" id="name" placeholder="Название комиссариата"
                            value="{{ old('name') }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    {{-- Начальник (НЕОБЯЗАТЕЛЬНЫЙ) --}}
                    <div class="relative" id="chief-select-wrapper">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Начальник
                        </label>

                        <input type="text" id="chief_employee_search" placeholder="Начните вводить ФИО (необязательно)"
                            value="{{ old('chief_employee_search') }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                                   focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                                   outline-none transition-colors text-[#060606]"
                            autocomplete="off">

                        <input type="hidden" name="chief_employee_id" id="chief_employee_id"
                            value="{{ old('chief_employee_id') }}">

                        <ul id="chief_employee_list"
                            class="absolute left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF]
                                   rounded-lg max-h-72 overflow-auto hidden shadow-lg">
                            <li class="px-4 py-2 cursor-pointer hover:bg-red-50 text-red-600 font-medium" data-id=""
                                data-name="">
                                ✕ Не назначать начальника
                            </li>
                            @foreach ($employees as $employee)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $employee->id }}"
                                    data-name="{{ trim($employee->full_name) }}">
                                    {{ $employee->full_name }}
                                    <span class="text-gray-400 text-sm">(ID: {{ $employee->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- X --}}
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по горизонтали *
                        </label>
                        <input type="number" name="longitude" id="longitude" placeholder="Ось х" max="200"
                            min="1" value="{{ old('longitude', $x) }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    {{-- Y --}}
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по вертикали *
                        </label>
                        <input type="number" name="latitude" id="latitude" placeholder="Ось y" max="120"
                            min="1" value="{{ old('latitude', $y) }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    {{-- Кнопка --}}
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('chief-select-wrapper');
            const input = document.getElementById('chief_employee_search');
            const hidden = document.getElementById('chief_employee_id');
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
                    const name = (item.dataset.name || '').toLowerCase();
                    const id = item.dataset.id || '';

                    if (item.dataset.id === '' || !query || name.includes(query) || id.includes(query)) {
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
                hidden.value = '';
                showList();
                filterList(input.value);
            });

            items.forEach(item => {
                item.addEventListener('click', () => {
                    const id = item.dataset.id || '';
                    const name = item.dataset.name || '';

                    // Если выбран пункт "Не назначать начальника" (пустой id)
                    if (id === '') {
                        input.value = '';
                        hidden.value = '';
                    } else {
                        input.value = name;
                        hidden.value = id;
                    }

                    hideList();
                });
            });
            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    hideList();
                }
            });
        });
    </script>
@endpush
