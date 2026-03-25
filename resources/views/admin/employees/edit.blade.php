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
                    <form id="employee-form" method="POST" action="{{ route('employees.update', [
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
                            $value = old($name) ?? ($column['default'] !== null ? $column['default'] : '');
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

                            // Подготовим массив существующих файлов для этого поля (если есть)
                            $existingFiles = [];
                            if (isset($employee) && ! empty($employee->person->$name)) {
                                $existingFiles = is_string($employee->person->$name)
                                    ? json_decode($employee->person->$name, true)
                                    : $employee->person->$name;
                                $existingFiles = is_array($existingFiles) ? $existingFiles : [];
                            }
                        @endphp

                        <div class="flex flex-col">
                            <label for="{{ $name }}" class="mb-1 text-sm font-medium text-[#060606]">
                                {{ $name }} {{ $isNullable ? "" : "*" }}
                            </label>

                            @if ($isTextarea)
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="3" placeholder="Введите {{ $name }}"
                                    class="px-3 py-2 bg-white border border-[#BFBFBF] rounded-lg text-sm focus:border-[#A60644] focus:ring-1 focus:ring-[#A60644]">{{ format_for_textarea($employee->person->$name) }}</textarea>
                                {{-- фото --}}
                            @elseif ($inputType === 'file')
                                {{-- контейнер с data-existing — используем для инициализации JS --}}
                                <div id="file-container-{{ $name }}" class="space-y-2" data-existing='@json($existingFiles)'>
                                    <div class="flex column gap-2">
                                        <input type="file" name="{{ $name }}[]" multiple class="flex-1 px-3 py-2 border rounded-lg"
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
                                                    $fullPath = storage_path('app/public/' . $filePath);
                                                @endphp
                                                <div id="existing-file-{{ $name }}-{{ $index }}" class="relative group w-20 h-20" title="{{ $fileName }}">
                                                    <div class="w-full h-full rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white">
                                                        @if($isImage && file_exists($fullPath))
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
                             @else
                                 <input id="{{ $name }}" name="{{ $name }}" type="{{ $inputType }}" value="{{ safe_value($employee->person->$name) }}"
                                     placeholder="Введите {{ $name }}" {{ $step }}
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
                                <input type="password" name="password" id="password"  placeholder="Введите пароль" value=""
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
    // Хранилище для новых выбранных файлов по каждой колонки
    let selectedFiles = {};
    // Хранилище для существующих файлов (из БД)
    let existingFilesObj = {};

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

    // сначала обновляем превью (использует selectedFiles)
    updatePreview(columnName);

    // очищаем чтобы можно было выбрать те же файлы
    input.value = '';

    // ✅ СИНХРОНИЗАЦИЯ С INPUT — выполняем после очистки value, чтобы переустановить файлы
    syncInputFiles(columnName);
}

    function updatePreview(columnName) {
        const previewContainer = document.getElementById(`preview-${columnName}`);
        previewContainer.innerHTML = '';

        if (!selectedFiles[columnName] || selectedFiles[columnName].length === 0) {
            return;
        }

        selectedFiles[columnName].forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'relative group w-fit flex items-center gap-2 p-1';
            item.setAttribute('data-file-index', index);

            // если изображение — показываем thumbnail, иначе — блок с именем
            if (file.type && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
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
                // не-изображения — показываем иконку/имя
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

        syncInputFiles(columnName); // ✅ важно

        updatePreview(columnName);
    }
}

    // Удаление существующего файла (из БД) — помечаем для контроллера и удаляем из DOM
    function markExistingFileForRemoval(columnName, index, filePath) {
        const elem = document.getElementById(`existing-file-${columnName}-${index}`);
        if (elem) elem.remove();

        // Обновляем объект существующих файлов (помечаем как null для соответствия индексов)
        if (!existingFilesObj[columnName]) existingFilesObj[columnName] = [];
        existingFilesObj[columnName][index] = null;

        // Добавляем скрытое поле для контроллера: removed_{column}_indexes[]
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

     // Функция для отправки формы — создаём правильный FormData
     function submitForm(columnName) {
         const form = document.getElementById('employee-form');
         const formData = new FormData(form);
 
         // Удаляем старые файловые поля
         formData.delete(`${columnName}[]`);
 
         // Добавляем все сохранённые файлы
         if (selectedFiles[columnName]) {
             selectedFiles[columnName].forEach(file => {
                 formData.append(`${columnName}[]`, file);
             });
         }
 
         // Отправляем форму
         fetch(form.action, {
             method: 'POST',
             body: formData,
             headers: {
                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
             }
         }).then(response => {
             if (response.redirected) {
                 window.location.href = response.url;
             }
         });
 
         return false;
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

// --- новый блок: перед отправкой формы синхронизируем все inputs ---
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('employee-form');
    if (!form) return;

    form.addEventListener('submit', function () {
        // синхронизируем все поля файлов из selectedFiles
        Object.keys(selectedFiles).forEach(columnName => {
            syncInputFiles(columnName);
        });
        // далее форма отправится обычным способом, и файлы будут в input'ах
    });
});

/* Вспомогательные функции */
function escapeHtml(str) {
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
        return {
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
            "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
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

// Инициализация existingFilesObj из data-existing контейнеров
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="file-container-"]').forEach(el => {
        const key = el.id.replace('file-container-', '');
        let data = el.getAttribute('data-existing');
        try {
            existingFilesObj[key] = data ? JSON.parse(data) : [];
        } catch (e) {
            existingFilesObj[key] = [];
        }
    });
});
</script>