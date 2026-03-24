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


        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
                <div class="p-6 md:p-8">
                    <form method="POST" action="{{ route('employees.update', [
        "id" => $employee->id
    ]) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method("PUT")

                        <div class="grid grid-cols-4 gap-4">
                            @foreach ($columns as $column)
                                @php
                                    $name = $column['name'];
                                    $type = $column['type'];
                                    $comment = $column["comment"] ?? null;
                                    $value = old($name)
                                        ?? ($column['default'] !== null ? $column['default'] : '');
                                    $isNullable = $column["nullable"];

                                    // Определяем input type
                                    $inputType = match ($comment) {
                                        'json' => 'textarea',
                                        'file' => 'file',
                                        default => match (true) {
                                                str_contains($type, 'int') => 'number',
                                                str_contains($type, 'decimal') => 'number',
                                                str_contains($type, 'varchar') => 'text',
                                                str_contains($type, 'date') => 'date',
                                                default => 'text',
                                            }
                                    };
                                    $isTextarea = in_array($inputType, ['textarea']);
                                    $step = str_contains($type, 'decimal') ? 'step=0.01' : null;
                                @endphp

                                <div class="flex flex-col">
                                    <label for="{{ $name }}" class="mb-1 text-sm font-medium text-[#060606]">
                                        {{ $name }} {{ $isNullable ? "" : "*" }}
                                    </label>

                                    @if ($isTextarea)
                                        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" placeholder="Введите {{ $name }}" {{ $isNullable ? "" : "required" }}
                                            class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">{{ format_for_textarea($employee->person->$name)}}</textarea>
                                        {{-- фото --}}
                                    @elseif ($inputType === 'file')
                                        <div id="file-container-{{ $name }}" class="space-y-2">
                                            <div class="flex column gap-2">
                                                <input type="file" name="{{ $name }}[]" multiple
                                                    class="flex-1 px-3 py-2 border rounded-lg"
                                                    onchange="previewMultipleFiles(this, '{{ $name }}')" {{ $isNullable ? "" : "required" }}>
                                            </div>
                                        </div>
                                        <div id="preview-{{ $name }}" class="mt-2 flex flex-wrap gap-2"></div>
                                         {{-- Существующие файлы из БД (при редактировании) --}}
                                        @if(isset($employee) && !empty($employee->person->$name))
                                            @php
                                                $existingFiles = is_string($employee->person->$name) 
                                                    ? json_decode($employee->person->$name, true) 
                                                    : $employee->person->$name;
                                                $existingFiles = is_array($existingFiles) ? $existingFiles : [];
                                            @endphp
                                            
                                    @if(count($existingFiles) > 0)
    <div class="mt-3">
        <p class="text-sm font-medium text-gray-700 mb-2">Существующие файлы:</p>
        <div id="existing-{{ $name }}" class="flex flex-wrap gap-2">
            @foreach($existingFiles as $index => $filePath)
                @php
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $fileName = basename($filePath);
                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                    $fullPath = storage_path('app/public/' . $filePath);
                    
                    // Определяем цвета для разных типов файлов
                    $bgColor = match ($extension) {
                        'pdf' => 'bg-red-100',
                        'doc', 'docx' => 'bg-blue-100',
                        'xls', 'xlsx', 'csv' => 'bg-green-100',
                        'ppt', 'pptx' => 'bg-orange-100',
                        'txt' => 'bg-gray-100',
                        'zip', 'rar', '7z' => 'bg-yellow-100',
                        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'bg-purple-100',
                        default => 'bg-gray-100',
                    };
                    
                    $textColor = match ($extension) {
                        'pdf' => 'text-red-600',
                        'doc', 'docx' => 'text-blue-600',
                        'xls', 'xlsx', 'csv' => 'text-green-600',
                        'ppt', 'pptx' => 'text-orange-600',
                        'txt' => 'text-gray-600',
                        'zip', 'rar', '7z' => 'text-yellow-600',
                        default => 'text-gray-600',
                    };
                @endphp
                
                <div id="existing-file-{{ $name }}-{{ $index }}" 
                    class="relative group w-20 h-20"
                    title="{{ $fileName }}">
                    
                    {{-- Контент --}}
                    <div class="w-full h-full rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white">
                        @if($isImage && file_exists($fullPath))
                            <img src="{{ asset('storage/' . $filePath) }}" 
                                class="w-full h-full object-cover"
                                alt="{{ $fileName }}">
                        @else
                            <div class="w-full h-full {{ $bgColor }} flex flex-col items-center justify-center">
                                <span class="text-xl font-bold {{ $textColor }} uppercase">{{ $extension ?: '?' }}</span>
                                <span class="text-xs text-gray-500 mt-1">{{ substr($fileName, -10) }}</span>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Кнопка удаления --}}
                    <button type="button" 
                        onclick="markExistingFileForRemoval('{{ $name }}', {{ $index }}, '{{ $filePath }}')" 
                        class="absolute -top-2.5 -right-2.5 z-10 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 hover:bg-red-600 hover:scale-110 shadow-md border border-white"
                        title="Удалить файл">
                        ✕
                    </button>
                </div>               
            @endforeach
        </div>
    </div>
@endif
                                        @endif
                                    @else
                                        <input id="{{ $name }}" name="{{ $name }}" type="{{ $inputType }}"
                                            value="{{ $employee->person->$name ?? safe_value($value) }}"
                                            placeholder="Введите {{ $name }}" {{ $step }} {{ $isNullable ? "" : "required" }}
                                            class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm">
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
                                    Пароль *
                                </label>
                                <input type="password" name="password" id="password" {{ !empty($employee->user->id) ? "" : "required" }} placeholder="Введите пароль" value=""
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
                                        <option value="{{ $role->id }}" {{ $employee->user->role->name === $role->name ? "selected" : "" }}>
                                            {{ $role->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>



                            <!-- Рабочий статус -->
                            <div>
                                <label for="work_status" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Рабочий статус *
                                </label>
                                <select name="work_status" id="work_status" required
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}" {{ $employee->work_status_id == $status->id ? "selected" : "" }}>
                                            {{ $status->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit"
                                class="px-4 py-2 bg-[#A60644] text-white text-sm rounded-lg hover:bg-[#A60644]/85">
                                Редактировать сотрудника
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection



<script>
    // Хранилище для файлов по каждой колонке
    let selectedFiles = {};

    function previewMultipleFiles(input, columnName) {
        const previewContainer = document.getElementById(`preview-${columnName}`);

        if (!selectedFiles[columnName]) {
            selectedFiles[columnName] = [];
        }

        // Добавляем новые файлы к существующим
        if (input.files) {
            Array.from(input.files).forEach(file => {
                // Проверяем, нет ли уже такого файла
                const exists = selectedFiles[columnName].some(f =>
                    f.name === file.name && f.size === file.size && f.lastModified === file.lastModified
                );
                if (!exists) {
                    selectedFiles[columnName].push(file);
                }
            });
        }

        // Обновляем отображение превью
        updatePreview(columnName);

        // Очищаем input
        input.value = '';
    }

    function updatePreview(columnName) {
        const previewContainer = document.getElementById(`preview-${columnName}`);
        previewContainer.innerHTML = '';

        if (!selectedFiles[columnName] || selectedFiles[columnName].length === 0) {
            return;
        }

        selectedFiles[columnName].forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'relative group w-fit';
                div.setAttribute('data-file-index', index);
                div.innerHTML = `
                <img src="${e.target.result}" class="w-16 h-16 object-cover border rounded">
                <button type="button" 
                    onclick="removeFileFromSelection('${columnName}', ${index})" 
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs opacity-0 group-hover:opacity-100">
                    ✕
                </button>
            `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    function removeFileFromSelection(columnName, index) {
        if (selectedFiles[columnName]) {
            selectedFiles[columnName].splice(index, 1);
            updatePreview(columnName);

            // 👇 Важно: обновляем input.files для стандартной отправки
            updateInputFiles(columnName);
        }
    }

    function updateInputFiles(columnName) {
        const input = document.querySelector(`input[name="${columnName}[]"]`);
        if (!input) return;

        // Создаём новый FileList
        const dt = new DataTransfer();
        if (selectedFiles[columnName]) {
            selectedFiles[columnName].forEach(file => {
                dt.items.add(file);
            });
        }
        input.files = dt.files;
    }

    function clearAllFiles(columnName) {
        if (confirm('Очистить все выбранные файлы?')) {
            selectedFiles[columnName] = [];
            updatePreview(columnName);
            updateInputFiles(columnName);
        }
    }

    function markExistingFileForRemoval(columnName, index, filePath) {
        // Удаляем элемент из DOM
        const fileElement = document.getElementById(`existing-file-${columnName}-${index}`);
        if (fileElement) {
            fileElement.remove();
        }

        // Добавляем скрытое поле для удаления
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `removed_${columnName}_existing_indexes[]`;
        hiddenInput.value = index;
        document.getElementById(`file-container-${columnName}`).appendChild(hiddenInput);

        const pathInput = document.createElement('input');
        pathInput.type = 'hidden';
        pathInput.name = `removed_${columnName}_existing_paths[]`;
        pathInput.value = filePath;
        document.getElementById(`file-container-${columnName}`).appendChild(pathInput);
    }

    // Инициализация для существующих файлов при загрузке страницы
    document.addEventListener('DOMContentLoaded', function () {
        // Если есть существующие файлы в БД, они уже отображаются
        // Ничего дополнительного не нужно
    });
</script>