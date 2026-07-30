@extends('layouts.main')

@section('header-title')
    Создание сотрудника
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="w-full p-6 mx-auto">
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
            <h1 class="text-2xl font-bold text-[#060606]">Создание сотрудника</h1>
            <p class="text-[#565A5B] mt-1">Создание данных сотрудника</p>
        </div>

        <!-- Форма -->
        <div class="mb-8">
            <form id="employee-form" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <input type="hidden" name="backUrl" value="{{ $backUrl }}">
                <input type="hidden" name="commissariatId" value="{{ $commissariatId }}">
                <input type="hidden" name="departmentId" value="{{ $departmentId }}">
                <input type="hidden" name="divisionId" value="{{ $divisionId }}">

                <div class="grid grid-cols-4 gap-4">
                    @foreach ($columns as $column)
                        @php
                            $name = $column['name'];
                            $type = $column['type'];
                            $comment = $column["comment"] ?? null;
                            $value = old($name) ?? ($column['default'] !== null ? $column['default'] : '');
                            $isNullable = $column["nullable"];
                            
                            // Нормализуем тип для проверки (убираем скобки и содержимое)
                            $normalizedType = preg_replace('/\(.*\)/', '', $type);
                            
                            // ПОРЯДОК ПРОВЕРОК ВАЖЕН!
                            // 1. Сначала проверяем комментарий file (даже для longtext)
                            $isFile = $comment === 'file';
                            
                            // 2. Затем проверяем json
                            $isJson = !$isFile && $comment === 'json';
                            
                            // 3. Затем boolean (tinyint(1) или boolean)
                            $isBoolean = !$isFile && !$isJson && (str_contains($type, 'tinyint(1)') || $type === 'boolean');
                            
                            // 4. Затем date
                            $isDate = !$isFile && !$isJson && !$isBoolean && str_contains($type, 'date');
                            
                            // 5. Затем textarea (для text, longtext и т.д., но не для file и json)
                            $isTextarea = !$isFile && !$isJson && !$isBoolean && !$isDate && (
                                str_contains($type, 'text') || 
                                str_contains($type, 'longtext') || 
                                str_contains($type, 'mediumtext')
                            );
                            
                            // 6. Затем decimal (числа с плавающей точкой) - проверяем normalized type
                            $isDecimal = !$isFile && !$isJson && !$isBoolean && !$isDate && !$isTextarea && (
                                str_contains($normalizedType, 'decimal') || 
                                str_contains($normalizedType, 'float') || 
                                str_contains($normalizedType, 'double')
                            );
                            
                            // 7. Затем integer (целые числа)
                            $isInteger = !$isFile && !$isJson && !$isBoolean && !$isDate && !$isTextarea && !$isDecimal && 
                                str_contains($normalizedType, 'int');
                            
                            // Определяем input type для обычных полей
                            $inputType = match (true) {
                                $isDate => 'date',
                                $isDecimal || $isInteger => 'number',
                                default => 'text',
                            };
                            
                            // Для JSON полей декодируем значение
                            $jsonValue = [];
                            if ($isJson && $value) {
                                $jsonValue = is_string($value) ? json_decode($value, true) : (array)$value;
                                if (!is_array($jsonValue)) $jsonValue = [];
                            }
                            
                            // Для date полей форматируем значение
                            $dateValue = '';
                            if ($isDate && !empty($value)) {
                                try {
                                    $dateValue = \Carbon\Carbon::parse($value)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    $dateValue = $value;
                                }
                            }
                            
                            // Для decimal/float полей форматируем значение (точки вместо запятых)
                            if ($isDecimal && !empty($value) && !is_numeric($value)) {
                                $value = str_replace(',', '.', $value);
                            }
                        @endphp

                        <div class="flex flex-col">
                            <label for="{{ $name }}" class="mb-1 text-sm font-medium text-[#060606]">
                                {{ $name }} {{ $isNullable ? "" : "*" }}
                            </label>

                            @if ($isFile)
                                {{-- Файлы --}}
                                <div id="file-container-{{ $name }}" class="space-y-2">
                                    <div class="flex column gap-2">
                                        <input type="file" name="{{ $name }}[]" multiple 
                                            class="flex-1 px-3 py-2 border rounded-lg focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]"
                                            onchange="previewMultipleFiles(this, '{{ $name }}')" 
                                            {{ $isNullable ? "" : "required" }}>
                                    </div>
                                </div>
                                <div id="preview-{{ $name }}" class="mt-2 flex flex-wrap gap-2"></div>
                            @elseif ($isJson)
                                {{-- JSON список с возможностью добавления через Enter --}}
                                <div class="json-list-container" data-field="{{ $name }}">
                                    <div class="json-tags-list flex flex-wrap gap-2 mb-2 p-2 border border-[#BFBFBF] rounded-lg min-h-[60px] bg-white">
                                        @foreach($jsonValue as $item)
                                            <span class="json-tag inline-flex items-center gap-1 px-2 py-1 bg-[#A60644]/10 text-[#A60644] rounded-lg text-sm">
                                                {{ $item }}
                                                <button type="button" class="remove-json-tag hover:text-red-600" onclick="removeJsonTag(this, '{{ $name }}')">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="flex gap-2">
                                        <input type="text" 
                                            class="json-input flex-1 px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]"
                                            placeholder="Введите значение и нажмите Enter"
                                            onkeypress="handleJsonKeypress(event, '{{ $name }}')">
                                    </div>
                                    <input type="hidden" name="{{ $name }}" value="{{ json_encode($jsonValue) }}" class="json-hidden">
                                </div>
                            @elseif ($isBoolean)
                                {{-- Boolean - выпадающий список Да / Нет --}}
                                <select id="{{ $name }}" name="{{ $name }}" {{ $isNullable ? "" : "required" }}
                                    class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">
                                    <option value="1" {{ old($name, $value) == '1' ? 'selected' : '' }}>Да</option>
                                    <option value="0" {{ old($name, $value) == '0' ? 'selected' : '' }}>Нет</option>
                                </select>
                            @elseif ($isDecimal)
                                {{-- Decimal поле (числа с плавающей точкой) --}}
                                <input id="{{ $name }}" name="{{ $name }}" type="number" 
                                    value="{{ $value }}"
                                    step="0.01"
                                    min="0"
                                    max="999999.99"
                                    placeholder="Введите {{ $name }}" 
                                    {{ $isNullable ? "" : "required" }}
                                    class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">
                            @elseif ($isInteger)
                                {{-- Integer поле (целое число) --}}
                                <input id="{{ $name }}" name="{{ $name }}" type="number" 
                                    value="{{ $value }}"
                                    step="1"
                                    placeholder="Введите {{ $name }}" 
                                    {{ $isNullable ? "" : "required" }}
                                    class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">
                            @elseif ($isTextarea)
                                {{-- Textarea поле --}}
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="3" placeholder="Введите {{ $name }}" {{ $isNullable ? "" : "required" }}
                                    class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">{{ $value }}</textarea>
                            @elseif ($isDate)
                                {{-- Date поле --}}
                                <input id="{{ $name }}" name="{{ $name }}" type="date" value="{{ $dateValue }}"
                                    placeholder="Введите {{ $name }}" {{ $isNullable ? "" : "required" }}
                                    class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">
                            @else
                                {{-- Обычное текстовое поле --}}
                                <input id="{{ $name }}" name="{{ $name }}" type="{{ $inputType }}" value="{{ safe_value($value) }}"
                                    placeholder="Введите {{ $name }}" {{ $isNullable ? "" : "required" }}
                                    class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">
                            @endif
                        </div>
                    @endforeach
                </div>

                <hr>

                <div class="grid grid-cols-4 gap-4">
                    <!-- Логин -->
                    <div>
                        <label for="login" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Логин *
                        </label>
                        <input type="text" name="login" id="login" placeholder="Введите логин" value="{{ old('login') }}"
                            required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>
                    
                    <!-- Пароль -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Пароль *
                        </label>
                        <input type="password" name="password" id="password" required placeholder="Введите пароль" value=""
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>
                    
                    <!-- Роль -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Роль
                        </label>
                        <select name="role" id="role"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ $role->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                        class="px-4 py-2 bg-[#A60644] text-white text-sm rounded-lg hover:bg-[#A60644]/85">
                        Создать сотрудника
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

<script>
    // Хранилище для файлов по каждой колонке
    let selectedFiles = {};

    function previewMultipleFiles(input, columnName) {
        if (!selectedFiles[columnName]) {
            selectedFiles[columnName] = [];
        }

        if (input.files) {
            Array.from(input.files).forEach(file => {
                const exists = selectedFiles[columnName].some(f =>
                    f.name === file.name &&
                    f.size === file.size &&
                    f.lastModified === file.lastModified
                );

                if (!exists) {
                    selectedFiles[columnName].push(file);
                }
            });
        }

        updatePreview(columnName);
        input.value = '';
        syncInputFiles(columnName);
    }

    function updatePreview(columnName) {
        const previewContainer = document.getElementById(`preview-${columnName}`);
        if (!previewContainer) return;
        
        previewContainer.innerHTML = '';

        if (!selectedFiles[columnName] || selectedFiles[columnName].length === 0) {
            return;
        }

        selectedFiles[columnName].forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'relative group w-fit flex items-center gap-2 p-1';
            item.setAttribute('data-file-index', index);

            if (file.type && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    item.innerHTML = `
                        <img src="${e.target.result}" class="w-16 h-16 object-cover border rounded" title="${file.name}">
                        <button type="button"
                            onclick="removeFileFromSelection('${columnName}', ${index})"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs opacity-0 group-hover:opacity-100">✕</button>
                    `;
                    previewContainer.appendChild(item);
                };
                reader.readAsDataURL(file);
            } else {
                item.innerHTML = `
                    <div class="w-16 h-16 flex items-center justify-center bg-[#F3F4F6] border rounded text-xs px-2 py-1"
                        title="${escapeHtml(file.name)}">
                        <span class="break-words text-[11px] max-w-[6rem]">${escapeHtml(shortName(file.name))}</span>
                    </div>
                    <button type="button"
                        onclick="removeFileFromSelection('${columnName}', ${index})"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs opacity-0 group-hover:opacity-100">✕</button>
                `;
                previewContainer.appendChild(item);
            }
        });
    }

    function removeFileFromSelection(columnName, index) {
        if (selectedFiles[columnName]) {
            selectedFiles[columnName].splice(index, 1);
            syncInputFiles(columnName);
            updatePreview(columnName);
        }
    }

    function syncInputFiles(columnName) {
        const container = document.getElementById(`file-container-${columnName}`);
        if (!container) return;
        const input = container.querySelector('input[type="file"]');
        if (!input) return;

        const dt = new DataTransfer();

        if (selectedFiles[columnName]) {
            selectedFiles[columnName].forEach(file => {
                dt.items.add(file);
            });
        }

        input.files = dt.files;
    }

    // ========== JSON LIST HANDLERS ==========
    function handleJsonKeypress(event, fieldName) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const input = event.target;
            const value = input.value.trim();
            
            if (value === '') return;
            
            addJsonTag(fieldName, value);
            input.value = '';
        }
    }
    
    function addJsonTag(fieldName, value) {
        const container = document.querySelector(`.json-list-container[data-field="${fieldName}"]`);
        if (!container) return;
        
        const tagsContainer = container.querySelector('.json-tags-list');
        const hiddenInput = container.querySelector('.json-hidden');
        
        let currentValue = [];
        try {
            currentValue = JSON.parse(hiddenInput.value || '[]');
        } catch (e) {
            currentValue = [];
        }
        
        if (!currentValue.includes(value)) {
            currentValue.push(value);
            hiddenInput.value = JSON.stringify(currentValue);
            
            const tag = document.createElement('span');
            tag.className = 'json-tag inline-flex items-center gap-1 px-2 py-1 bg-[#A60644]/10 text-[#A60644] rounded-lg text-sm';
            tag.innerHTML = `
                ${escapeHtml(value)}
                <button type="button" class="remove-json-tag hover:text-red-600" onclick="removeJsonTag(this, '${fieldName}')">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            tagsContainer.appendChild(tag);
        }
    }
    
    function removeJsonTag(button, fieldName) {
        const tag = button.closest('.json-tag');
        const container = document.querySelector(`.json-list-container[data-field="${fieldName}"]`);
        if (!container || !tag) return;
        
        const value = tag.childNodes[0]?.nodeValue?.trim() || '';
        const hiddenInput = container.querySelector('.json-hidden');
        
        let currentValue = [];
        try {
            currentValue = JSON.parse(hiddenInput.value || '[]');
        } catch (e) {
            currentValue = [];
        }
        
        const index = currentValue.indexOf(value);
        if (index !== -1) {
            currentValue.splice(index, 1);
            hiddenInput.value = JSON.stringify(currentValue);
        }
        
        tag.remove();
    }

    // Перед отправкой формы синхронизируем все inputs
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('employee-form');
        if (!form) return;

        form.addEventListener('submit', function() {
            Object.keys(selectedFiles).forEach(columnName => {
                syncInputFiles(columnName);
            });
        });
    });

    function escapeHtml(str) {
        return String(str).replace(/[&<>"'`=\/]/g, function(s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                '=': '&#x3D;'
            }[s];
        });
    }

    function shortName(name, len = 20) {
        if (name.length <= len) return name;
        const extIndex = name.lastIndexOf('.');
        const ext = extIndex !== -1 ? name.slice(extIndex) : '';
        const base = name.slice(0, len - ext.length - 3);
        return base + '...' + ext;
    }
</script>

<style>
.json-tag {
    transition: all 0.2s ease;
}
.json-tag:hover {
    background-color: #A60644;
    color: white;
}
.json-tag:hover button svg {
    stroke: white;
}
.remove-json-tag {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    display: inline-flex;
    align-items: center;
}
</style>