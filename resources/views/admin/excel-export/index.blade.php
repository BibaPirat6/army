@extends('layouts.main')

@section('header-title')
    Экспорт в Excel
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="space-y-4">
            
            <!-- Сотрудники -->
            <div class="bg-white border rounded-lg p-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-medium">Сотрудники</span>
                </div>
                <a href="{{ route('excel-export.employee') }}" 
                    class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Скачать
                </a>
            </div>

            <!-- Структура (с выбором комиссариата) -->
            <div class="bg-white border rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="font-medium">Структура комиссариата</span>
                    </div>
                </div>
                
                <!-- Выбор комиссариата -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Выберите комиссариат
                    </label>
                    
                    <div class="relative">
                        <input type="text" id="structure_commissariat_search" 
                            placeholder="Выберите комиссариат"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            autocomplete="off">
                        
                        <input type="hidden" name="structure_commissariat_id" id="structure_commissariat_id">
                        
                        <ul id="structure_commissariat_list"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-lg max-h-72 overflow-auto hidden">
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 text-red-500" data-id="" data-name="">
                                Очистить
                            </li>
                            @foreach ($commissariats as $commissariat)
                                <li class="px-4 py-2 cursor-pointer hover:bg-gray-100" 
                                    data-id="{{ $commissariat->id }}"
                                    data-name="{{ $commissariat->name }}">
                                    {{ $commissariat->name }}
                                    <span class="text-gray-400 text-sm">(ID: {{ $commissariat->id }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                
                <!-- Кнопка экспорта -->
                <div class="flex justify-end">
                    <button type="button" id="exportStructureBtn" disabled
                        class="px-4 py-2 bg-gray-300 text-gray-500 text-sm rounded cursor-not-allowed transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Скачать структуру
                    </button>
                </div>
            </div>

            <!-- Остальные карточки можно добавить позже -->
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Скрипт для выбора комиссариата в структуре
    const input = document.getElementById('structure_commissariat_search');
    const hiddenInput = document.getElementById('structure_commissariat_id');
    const list = document.getElementById('structure_commissariat_list');
    const exportBtn = document.getElementById('exportStructureBtn');
    const items = list.querySelectorAll('li');

    function showList() {
        list.classList.remove('hidden');
    }

    function hideList() {
        list.classList.add('hidden');
    }

    function updateExportButton() {
        const commissariatId = hiddenInput.value;
        if (commissariatId && commissariatId !== '') {
            exportBtn.disabled = false;
            exportBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            exportBtn.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700', 'cursor-pointer');
            
            // Обновляем ссылку
            exportBtn.onclick = () => {
                window.location.href = "{{ url('/excel-export/structure') }}/" + commissariatId;
            };
        } else {
            exportBtn.disabled = true;
            exportBtn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700', 'cursor-pointer');
            exportBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            exportBtn.onclick = null;
        }
    }

    function filterList(value) {
        const query = value.toLowerCase().trim();
        let hasVisible = false;

        items.forEach(item => {
            const isClearItem = item.getAttribute('data-id') === '';
            
            if (isClearItem) {
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
        updateExportButton();
        showList();
        filterList(input.value);
    });

    items.forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            
            const id = item.dataset.id;
            const name = item.dataset.name;
            
            if (id === '') {
                // Очистка
                input.value = '';
                hiddenInput.value = '';
            } else {
                input.value = name;
                hiddenInput.value = id;
            }
            
            updateExportButton();
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
@endsection