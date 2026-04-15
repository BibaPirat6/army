@extends('layouts.main')

@section('header-title')
    Изменение коммиссариата
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{$backUrl ?? route('commissariats.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Изменение комиссариата</h1>
            <p class="text-[#565A5B] mt-1">Редактирование комиссариата: "{{ $commissariat->name }}"</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('commissariats.update', $commissariat->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    <!-- Название комиссариата -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название комиссариата
                        </label>
                        <input type="text" name="name" id="name" placeholder="Название комиссариата"
                            value="{{ old('name', $commissariat->name) }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

            
                    {{-- начальник --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-[#565A5B] mb-2">
                            Начальник (опционально)
                        </label>

                        {{-- visible input (необязательное) --}}
                        <input type="text" id="chief_employee_search" placeholder="Начните вводить ФИО" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg
                   focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644]
                   outline-none transition-colors text-[#060606]" autocomplete="off"
                   value="{{ old('chief_employee_id', $commissariat->getChiefAttribute() ? $commissariat->getChiefAttribute()->getFullNameAttribute() : '') }}">

                        {{-- hidden value (необязательное) --}}
                        <input type="hidden" name="chief_employee_id" id="chief_employee_id"
                            value="{{ old('chief_employee_id', $commissariat->getChiefAttribute() ? $commissariat->getChiefAttribute()->id : '') }}"
                            data-original="{{ $commissariat->getChiefAttribute() ? $commissariat->getChiefAttribute()->id : '' }}"
                            data-original-exists="{{ $commissariat->getChiefAttribute() ? '1' : '0' }}">
                        {{-- dropdown --}}
                        <ul id="chief_employee_list" class="relative z-10 mt-1 w-full bg-white border border-[#BFBFBF]
                   rounded-lg max-h-72 overflow-auto hidden">

                            {{-- опция "Не назначать" --}}
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id=""
                                data-name="Не назначать" data-static="true">
                                Не назначать
                            </li>

                            @foreach ($employees as $employee)
                                                    <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" data-id="{{ $employee->id }}" data-name="{{ trim(
                                    $employee->getFullNameAttribute()
                                ) }}" data-search="{{ $employee->id }}">
                                                        @if ($employee->person)
                                                            {{ $employee->getFullNameAttribute()}}
                                                            <span class="text-gray-400">(ID: {{ $employee->id ?? '*' }})</span>
                                                        @else
                                                            <span class="text-gray-400">Без ФИО (ID: {{ $employee->id }})</span>
                                                        @endif
                                                    </li>
                            @endforeach
                        </ul>

                        {{-- блок выбора статуса — показывается только при смене начальника (если ранее начальник был) --}}
                        <div id="chief_status_wrapper" class="mt-3 hidden">
                            <label for="chief_employee_position_status_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Статус назначения
                            </label>
                            <select name="chief_employee_position_status_id" id="chief_employee_position_status_id"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                <option value="">— выберите статус —</option>
                                @foreach($employeePositionStatuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ old('chief_employee_position_status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- x -->
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по горизонтали
                        </label>
                        <input type="number" name="longitude" id="longitude" placeholder="Ось х" max="200" min="1"
                            value="{{ old('longitude', $commissariat->longitude) }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- y -->
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Координаты по вертикали
                        </label>
                        <input type="number" name="latitude" id="latitude" placeholder="Ось y" max="120" min="1"
                            value="{{ old('latitude', $commissariat->latitude) }}"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>



                    <!-- Кнопка отправки -->
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Обновить комиссариат
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

        const statusWrapper = document.getElementById('chief_status_wrapper');
        const originalId = hiddenInput.dataset.original || '';
        const originalExists = hiddenInput.dataset.originalExists === '1';

        function showList() {
            list.classList.remove('hidden');
        }

        function hideList() {
            list.classList.add('hidden');
        }

        function hideStatus() {
            statusWrapper.classList.add('hidden');
        }

        function showStatus() {
            statusWrapper.classList.remove('hidden');
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
            hideStatus();
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
                    hideStatus();

                    if (wasNotEmpty) {
                        showList();
                        filterList('');
                    } else {
                        hideList();
                    }

                    return;
                }

                const selectedId = item.dataset.id || '';
                input.value = item.dataset.name || `ID ${selectedId}`;
                hiddenInput.value = selectedId;
                hideList();

                // показать выбор статуса только если ранее начальник был (originalExists)
                // и выбран новый (selectedId !== originalId)
                if (originalExists && selectedId && selectedId !== originalId) {
                    showStatus();
                } else {
                    hideStatus();
                }
            });
        });

        // При загрузке — если в old есть выбор и он отличается от оригинала, показать статус (учёт возврата с ошибкой)
        const currentSelectedId = hiddenInput.value || '';
        if (originalExists && currentSelectedId && currentSelectedId !== originalId) {
            showStatus();
        } else {
            hideStatus();
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                hideList();
            }
        });
    });
</script>