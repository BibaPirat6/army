@extends('layouts.main')

@section('header-title')
    Редактирование сотрудника
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
            <h1 class="text-2xl font-bold text-[#060606]">Редактирование сотрудника</h1>
            <p class="text-[#565A5B] mt-1">Редактирование данных сотрудника</p>
        </div>

        <div class="mb-8">
            <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
                <div class="p-6 md:p-8">
                    <form id="employee-form" method="POST" action="{{ route('employees.update', ['id' => $employee->id]) }}" 
                        enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method("PUT")

                        <input type="hidden" name="backUrl" value="{{ $backUrl }}">
                        
                        <div class="grid grid-cols-4 gap-4">
                            @foreach ($columns as $column)
                                @php
                                    $name = $column['name'];
                                    $type = $column['type'];
                                    $comment = $column["comment"] ?? null;
                                    $isNullable = $column["nullable"];
                                    
                                    // Получаем значение из модели Person
                                    $currentValue = $employee->person->$name ?? null;
                                    $value = old($name) ?? ($currentValue ?? ($column['default'] ?? ''));
                                    
                                    // ПОРЯДОК ПРОВЕРОК ВАЖЕН!
                                    // 1. Сначала проверяем комментарий file
                                    $isFile = $comment === 'file';
                                    
                                    // 2. Затем проверяем json
                                    $isJson = !$isFile && $comment === 'json';
                                    
                                    // 3. Затем boolean
                                    $isBoolean = !$isFile && !$isJson && (str_contains($type, 'tinyint(1)') || $type === 'boolean');
                                    
                                    // 4. Затем date
                                    $isDate = !$isFile && !$isJson && str_contains($type, 'date');
                                    
                                    // 5. Затем textarea
                                    $isTextarea = !$isFile && !$isJson && (str_contains($type, 'text'));
                                    
                                    // Определяем input type для обычных полей
                                    $inputType = match (true) {
                                        $isDate => 'date',
                                        str_contains($type, 'int') && !$isBoolean => 'number',
                                        str_contains($type, 'decimal') => 'number',
                                        default => 'text',
                                    };
                                    
                                    $step = str_contains($type, 'decimal') ? 'step="0.01"' : '';
                                    
                                    // Для JSON полей декодируем значение
                                    $jsonValue = [];
                                    if ($isJson && $currentValue) {
                                        $jsonValue = is_string($currentValue) ? json_decode($currentValue, true) : (array)$currentValue;
                                        if (!is_array($jsonValue)) $jsonValue = [];
                                    }
                                    
                                    // Для файлов - подготовка существующих файлов
                                    $existingFiles = [];
                                    if ($isFile && $currentValue) {
                                        $existingFiles = is_string($currentValue) ? json_decode($currentValue, true) : (array)$currentValue;
                                        $existingFiles = is_array($existingFiles) ? $existingFiles : [];
                                    }
                                @endphp

                                <div class="flex flex-col">
                                    <label for="{{ $name }}" class="mb-1 text-sm font-medium text-[#060606]">
                                        {{ $name }} {{ $isNullable ? "" : "*" }}
                                    </label>

                                    @if ($isFile)
                                        {{-- Файлы --}}
                                        <div id="file-container-{{ $name }}" class="space-y-2" data-existing='@json($existingFiles)'>
                                            <div class="flex column gap-2">
                                                <input type="file" name="{{ $name }}[]" multiple 
                                                    class="flex-1 px-3 py-2 border rounded-lg focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]"
                                                    onchange="previewMultipleFiles(this, '{{ $name }}')">
                                            </div>
                                        </div>
                                        <div id="preview-{{ $name }}" class="mt-2 flex flex-wrap gap-2"></div>

                                        {{-- Существующие файлы из БД --}}
                                        @if(count($existingFiles) > 0)
                                            <div class="mt-3">
                                                <p class="text-sm font-medium text-gray-700 mb-2">Существующие файлы:</p>
                                                <div id="existing-{{ $name }}" class="flex flex-wrap gap-2">
                                                    @foreach($existingFiles as $index => $filePath)
                                                        @php
                                                            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                                            $fileName = basename($filePath);
                                                            $isImage = in_array($extension, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                        @endphp
                                                        <div id="existing-file-{{ $name }}-{{ $index }}" class="relative group w-20 h-20" title="{{ $fileName }}">
                                                            <div class="w-full h-full rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white">
                                                                @if($isImage)
                                                                    <img src="{{ asset('storage/' . $filePath) }}" class="w-full h-full object-cover" alt="{{ $fileName }}">
                                                                @else
                                                                    <div class="w-full h-full bg-gray-100 flex flex-col items-center justify-center">
                                                                        <span class="text-xs text-gray-600">{{ strtoupper($extension ?: '?') }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <button type="button"
                                                                onclick="markExistingFileForRemoval('{{ $name }}', {{ $index }}, '{{ $filePath }}')"
                                                                class="absolute -top-2.5 -right-2.5 z-10 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 hover:scale-110 shadow-md border border-white"
                                                                title="Удалить файл">✕</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @elseif ($isJson)
    {{-- JSON список --}}
    @php
        // Для отправки в форме нужно преобразовать массив в строку с разделителями
        $jsonValueForInput = '';
        if ($isJson && $currentValue) {
            if (is_array($currentValue)) {
                $jsonValueForInput = implode("\n", $currentValue);
            } elseif (is_string($currentValue)) {
                $decoded = json_decode($currentValue, true);
                if (is_array($decoded)) {
                    $jsonValueForInput = implode("\n", $decoded);
                } else {
                    $jsonValueForInput = $currentValue;
                }
            }
        }
    @endphp
    
    <div class="json-list-container" data-field="{{ $name }}">
        <div class="json-tags-list flex flex-wrap gap-2 mb-2 p-2 border border-[#BFBFBF] rounded-lg min-h-[60px] bg-white">
            @forelse($jsonValue as $item)
                <span class="json-tag inline-flex items-center gap-1 px-2 py-1 bg-[#A60644]/10 text-[#A60644] rounded-lg text-sm">
                    {{ $item }}
                    <button type="button" class="remove-json-tag hover:text-red-600" onclick="removeJsonTag(this, '{{ $name }}')">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @empty
                <span class="text-gray-400 text-sm italic">Нет данных</span>
            @endforelse
        </div>
        <div class="flex gap-2">
            <input type="text" 
                class="json-input flex-1 px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]"
                placeholder="Введите значение и нажмите Enter"
                onkeypress="handleJsonKeypress(event, '{{ $name }}')">
        </div>
        <input type="hidden" name="{{ $name }}" value="{{ $jsonValueForInput }}" class="json-hidden-text">
    </div>

                                    @elseif ($isBoolean)
                                        {{-- Boolean select --}}
                                        <select id="{{ $name }}" name="{{ $name }}" {{ $isNullable ? "" : "required" }}
                                            class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">
                                            <option value="1" {{ old($name, $value) == '1' ? 'selected' : '' }}>Да</option>
                                            <option value="0" {{ old($name, $value) == '0' ? 'selected' : '' }}>Нет</option>
                                        </select>
                                    @elseif ($isTextarea)
                                        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" placeholder="Введите {{ $name }}" {{ $isNullable ? "" : "required" }}
                                            class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">{{ format_for_textarea($value) }}</textarea>
                                    @else
                                        <input id="{{ $name }}" name="{{ $name }}" type="{{ $inputType }}" value="{{ safe_value($value) }}"
                                            placeholder="Введите {{ $name }}" {{ $step }} {{ $isNullable ? "" : "required" }}
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
                                <input type="text" name="login" id="login" placeholder="Введите логин"
                                    value="{{ old('login', $employee->user->login) }}" required
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            </div>

                            <!-- Пароль -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Пароль
                                </label>
                                <input type="password" name="password" id="password" placeholder="Оставьте пустым, чтобы не менять"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                <p class="text-xs text-gray-500 mt-1">Оставьте пустым, чтобы оставить текущий пароль</p>
                            </div>

                            <!-- Роль -->
                            <div>
                                <label for="role" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Роль
                                </label>
                                <select name="role" id="role"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ $employee->user->role->name === $role->name ? "selected" : "" }}>
                                            {{ $role->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit"
                                class="px-4 py-2 bg-[#A60644] text-white text-sm rounded-lg hover:bg-[#A60644]/85">
                                Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    // Хранилище для новых выбранных файлов по каждой колонке
    let selectedFiles = {};
    // Хранилище для существующих файлов (из БД)
    let existingFilesObj = {};

    // ========== Файловые функции ==========
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

    // Удаление существующего файла (из БД)
    function markExistingFileForRemoval(columnName, index, filePath) {
        const elem = document.getElementById(`existing-file-${columnName}-${index}`);
        if (elem) elem.remove();

        if (!existingFilesObj[columnName]) existingFilesObj[columnName] = [];
        existingFilesObj[columnName][index] = null;

        const container = document.getElementById(`file-container-${columnName}`);
        if (!container) return;
        
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `removed_${columnName}_indexes[]`;
        hidden.value = index;
        container.appendChild(hidden);

        const pathHidden = document.createElement('input');
        pathHidden.type = 'hidden';
        pathHidden.name = `removed_${columnName}_existing_paths[]`;
        pathHidden.value = filePath;
        container.appendChild(pathHidden);
    }

    // ========== JSON LIST FUNCTIONS ==========
    // ========== JSON LIST FUNCTIONS ==========
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
    const hiddenInput = container.querySelector('.json-hidden-text');
    
    // Получаем текущий массив из скрытого поля (текст с разделителями)
    let currentValue = [];
    const rawValue = hiddenInput.value;
    if (rawValue && rawValue.trim() !== '') {
        currentValue = rawValue.split('\n').filter(v => v.trim() !== '');
    }
    
    if (!currentValue.includes(value)) {
        currentValue.push(value);
        hiddenInput.value = currentValue.join('\n');
        
        // Удаляем заглушку "Нет данных" если она есть
        const emptySpan = tagsContainer.querySelector('.text-gray-400');
        if (emptySpan && emptySpan.classList.contains('text-gray-400')) {
            emptySpan.remove();
        }
        
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
    
    // Получаем текст значения
    let value = '';
    for (let node of tag.childNodes) {
        if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
            value = node.textContent.trim();
            break;
        }
    }
    
    const hiddenInput = container.querySelector('.json-hidden-text');
    
    // Получаем текущий массив из скрытого поля
    let currentValue = [];
    const rawValue = hiddenInput.value;
    if (rawValue && rawValue.trim() !== '') {
        currentValue = rawValue.split('\n').filter(v => v.trim() !== '');
    }
    
    const index = currentValue.indexOf(value);
    if (index !== -1) {
        currentValue.splice(index, 1);
        hiddenInput.value = currentValue.join('\n');
    }
    
    tag.remove();
    
    // Если не осталось тегов, показываем заглушку
    const tagsContainer = container.querySelector('.json-tags-list');
    if (tagsContainer.children.length === 0) {
        tagsContainer.innerHTML = '<span class="text-gray-400 text-sm italic">Нет данных</span>';
    }
}

    // ========== Инициализация ==========
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('employee-form');
        if (!form) return;

        // Инициализация existingFilesObj из data-existing
        document.querySelectorAll('[id^="file-container-"]').forEach(el => {
            const key = el.id.replace('file-container-', '');
            let data = el.getAttribute('data-existing');
            try {
                existingFilesObj[key] = data ? JSON.parse(data) : [];
            } catch (e) {
                existingFilesObj[key] = [];
            }
        });

        // Синхронизация файлов перед отправкой
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