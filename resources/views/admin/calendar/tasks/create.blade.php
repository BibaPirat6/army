@extends('layouts.main')

@section('header-title')
    Создание задачи
@endsection

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <a href="{{ route('calendar.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Назад к календарю
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Новая задача</h1>

            <form action="{{ route('calendar.tasks.store') }}" method="POST" enctype="multipart/form-data" id="taskForm">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                {{-- Ответственный с поиском и выпадающим списком --}}
                <div class="mb-4 relative" id="responsible-container">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ответственный</label>
                    <input type="text" id="responsible-search" 
                        placeholder="Поиск по ФИО, должности или подразделению..."
                        autocomplete="off"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <input type="hidden" name="employee_position_id" id="employee_position_id" value="{{ old('employee_position_id') }}">
                    <div id="responsible-dropdown" 
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Цвет</label>
                        <input type="color" name="color" id="color" value="{{ old('color', '#3788d8') }}"
                            class="h-10 w-full rounded-lg border-gray-300 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Квота</label>
                        <input type="number" name="quota" id="quota" min="1" value="{{ old('quota', 1) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата начала *</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $startDate) }}" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Дата окончания *</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $startDate) }}" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Файлы с валидацией --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Файлы</label>
                    <input type="file" name="files[]" multiple id="file-input"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-400 mt-1">Максимум 10 МБ на файл</p>
                    <div id="file-errors" class="mt-2 space-y-1"></div>
                    @error('files.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('calendar.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Отмена</a>
                    <button type="submit" id="submit-btn"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Создать</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Данные для поиска
const employeePositions = @json($employeePositions);

// === ЛОГИКА ДАТ ===
const startDateInput = document.getElementById('start_date');
const endDateInput = document.getElementById('end_date');

startDateInput.addEventListener('change', function() {
    endDateInput.value = this.value;
});

endDateInput.addEventListener('change', function() {
    if (this.value < startDateInput.value) {
        this.value = startDateInput.value;
        alert('Дата окончания не может быть раньше даты начала');
    }
});

// === ВАЛИДАЦИЯ ФАЙЛОВ ===
const fileInput = document.getElementById('file-input');
const fileErrorsDiv = document.getElementById('file-errors');
const submitBtn = document.getElementById('submit-btn');
const taskForm = document.getElementById('taskForm');

let validFilesCount = 0;

function validateFiles() {
    const files = Array.from(fileInput.files);
    const maxSize = 10 * 1024 * 1024; // 10 МБ
    const errors = [];
    const validFiles = [];
    
    // Очищаем предыдущие ошибки
    fileErrorsDiv.innerHTML = '';
    
    files.forEach(file => {
        if (file.size > maxSize) {
            errors.push(`Файл "${file.name}" превышает 10 МБ (${(file.size / 1024 / 1024).toFixed(2)} МБ)`);
        } else {
            validFiles.push(file);
        }
    });
    
    // Показываем ошибки
    if (errors.length > 0) {
        errors.forEach(error => {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-sm text-red-600 bg-red-50 p-2 rounded';
            errorDiv.textContent = error;
            fileErrorsDiv.appendChild(errorDiv);
        });
    }
    
    // Обновляем input только с валидными файлами
    if (validFiles.length !== files.length) {
        const dataTransfer = new DataTransfer();
        validFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
        
        if (validFiles.length === 0) {
            fileInput.value = '';
        }
        
        if (validFiles.length > 0) {
            const successDiv = document.createElement('div');
            successDiv.className = 'text-sm text-green-600 bg-green-50 p-2 rounded mt-1';
            successDiv.textContent = `Выбрано ${validFiles.length} файл(ов) для загрузки`;
            fileErrorsDiv.appendChild(successDiv);
        }
    }
    
    validFilesCount = validFiles.length;
}

// Валидация при выборе файлов
fileInput.addEventListener('change', validateFiles);

// Валидация перед отправкой формы
taskForm.addEventListener('submit', function(e) {
    // Проверяем файлы
    const files = Array.from(fileInput.files);
    const maxSize = 10 * 1024 * 1024;
    let hasInvalidFile = false;
    
    for (const file of files) {
        if (file.size > maxSize) {
            hasInvalidFile = true;
            break;
        }
    }
    
    if (hasInvalidFile) {
        e.preventDefault();
        alert('Есть файлы превышающие 10 МБ. Пожалуйста, удалите их или выберите другие файлы.');
        return;
    }
    
    // Блокируем кнопку, чтобы избежать двойной отправки
    submitBtn.disabled = true;
    submitBtn.textContent = 'Сохранение...';
});

// === ВЫПАДАЮЩИЙ СПИСОК С ПОИСКОМ ===
const searchInput = document.getElementById('responsible-search');
const hiddenInput = document.getElementById('employee_position_id');
const dropdown = document.getElementById('responsible-dropdown');

function renderDropdown(items) {
    if (!items.length) {
        dropdown.innerHTML = '<div class="p-3 text-gray-500 text-sm text-center">Ничего не найдено</div>';
        return;
    }
    
    dropdown.innerHTML = items.map(item => `
        <div class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-100 last:border-0" 
             data-id="${item.id}"
             data-name="${item.full_name.replace(/'/g, "\\'")}">
            <div class="font-medium text-gray-800">${item.full_name}</div>
            <div class="text-xs text-gray-500 mt-0.5">
                <span class="inline-block mr-3">📌 ${item.position}</span>
                <span>🏛 ${item.unit}</span>
            </div>
        </div>
    `).join('');
    
    dropdown.querySelectorAll('[data-id]').forEach(el => {
        el.addEventListener('click', () => {
            hiddenInput.value = el.dataset.id;
            searchInput.value = el.dataset.name;
            dropdown.classList.add('hidden');
        });
    });
}

function filterEmployees(searchText) {
    const query = searchText.toLowerCase().trim();
    
    if (!query) {
        renderDropdown(employeePositions);
        return;
    }
    
    const filtered = employeePositions.filter(emp => 
        emp.search_text.includes(query)
    );
    
    renderDropdown(filtered);
}

// При фокусе - показываем всех
searchInput.addEventListener('focus', () => {
    dropdown.classList.remove('hidden');
    renderDropdown(employeePositions);
});

// При вводе - фильтруем
searchInput.addEventListener('input', (e) => {
    filterEmployees(e.target.value);
});

// Закрытие при клике вне
document.addEventListener('click', (e) => {
    if (!document.getElementById('responsible-container')?.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Если есть старое значение (при ошибке валидации)
@if(old('employee_position_id'))
    const oldValue = {{ old('employee_position_id') }};
    const selected = employeePositions.find(emp => emp.id == oldValue);
    if (selected) {
        searchInput.value = selected.full_name;
        hiddenInput.value = selected.id;
    }
@endif
</script>
@endpush