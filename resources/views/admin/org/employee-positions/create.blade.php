@extends('layouts.main')

@section('header-title')
    Назначение должности сотруднику
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('employees.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Назначение должности</h1>
            <p class="text-[#565A5B] mt-1">
                Сотрудник: <span class="font-semibold text-[#A60644]">{{ $employee->person->фамилия ?? '' }}
                    {{ $employee->person->имя ?? '' }}</span>
            </p>
        </div>


        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('commissariat-positions.index') }}" method="GET" class="space-y-6">
                    @csrf
                    <input type="hidden" name="back_url" value="{{ $backUrl }}">
                    <input type="hidden" name="employeeId" value="{{ $employeeId }}">

                    <!-- Комиссариат -->
                    <div class="relative" id="commissariat-select">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Комиссариат <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="commissariat_search" placeholder="Начните вводить название комиссариата"
                            autocomplete="off" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none">
                        <input type="hidden" name="commissariat_id" id="commissariat_id2" required>
                        <ul id="commissariat_list"
                            class="relative left-0 right-0 z-50 mt-1 bg-white border border-[#BFBFBF] rounded-lg max-h-72 overflow-auto hidden shadow-lg">
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id="" data-name=""
                                data-static="true">Очистить</li>
                            @foreach ($commissariats as $commissariat)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $commissariat->id }}"
                                    data-name="{{ $commissariat->name }}">
                                    {{ $commissariat->name }}
                                    <span class="text-gray-400">(ID: {{ $commissariat->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                        @error('commissariat_id2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // DOM элементы
            const commissariatInput = document.getElementById('commissariat_search');
            const commissariatHidden = document.getElementById('commissariat_id2');
            const commissariatList = document.getElementById('commissariat_list');

            // Универсальная функция фильтрации
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

            function openList() {
                commissariatList.classList.remove('hidden');
            }

            function closeList() {
                commissariatList.classList.add('hidden');
            }

            // Обработчики для комиссариата
            commissariatInput.addEventListener('focus', () => {
                filterList(commissariatInput, commissariatList);
                openList();
            });

            commissariatInput.addEventListener('input', () => {
                commissariatHidden.value = '';
                filterList(commissariatInput, commissariatList);
                openList();
            });

            // Клик по элементам списка
            commissariatList.querySelectorAll('li').forEach(item => {
                item.addEventListener('click', () => {
                    if (item.dataset.static === 'true') {
                        commissariatInput.value = '';
                        commissariatHidden.value = '';
                    } else {
                        commissariatInput.value = item.dataset.name;
                        commissariatHidden.value = item.dataset.id;
                    }
                    closeList();
                });
            });

            // Закрытие списка при клике вне
            document.addEventListener('click', (e) => {
                if (!e.target.closest('#commissariat-select')) {
                    closeList();
                }
            });
        });
    </script>
@endpush