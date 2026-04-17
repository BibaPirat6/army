@extends('layouts.main')

@section('header-title')
    {{ $employee->getFullNameAttribute()}}
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
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
            <h1 class="text-2xl font-bold text-[#060606]">Информация о сотруднике</h1>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                 <h2 class="text-2xl font-bold text-[#060606] border-l-4 border-[#A60644] pl-4 py-1 mb-4">
                        {{ $employee->getFullNameAttribute()}}
                    </h2>
                {{-- сотрудник --}}
                                    <details class="group bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg overflow-hidden mb-4">
                                        <!-- Заголовок аккордеона -->
                                        <summary
                                            class="flex items-center justify-between cursor-pointer p-4 hover:bg-[#A60644]/10 transition-colors duration-200">
                                            <h1 class="text-xl font-bold text-[#060606]">Данные сотрудника</h1>
                                            <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                                </path>
                                            </svg>
                                        </summary>

                                        {{-- person --}}
                                        <!-- Содержимое аккордеона -->
                                        <div class="p-4 space-y-3 animate-fadeIn">
                                            @foreach ($columns as $column)
                                                <div class="flex items-center justify-between">
                                                    <span class="font-medium text-[#565A5B]">{{ $column['name'] }}</span>

                                                    {{-- Обработка разных типов --}}
                                                    @php
                                                        $columnName = $column['name'];
                                                        // использовать $employee (данные текущего отображаемого сотрудника)
                                                        $value = $employee->person->{$columnName} ?? null;
                                                        // Определяем тип колонки — согласовано с employees/edit.blade.php:
                                                        // сначала используем явный $column['type'], иначе смотрим точный комментарий ($column['comment'])
                                                        // где админ может указывать 'file' или 'json'. Далее — fallback по типу столбца.
                                                        $columnType = 'text';
                                                        $comment = $column['comment'] ?? null;
                                                        if (!empty($column['type'])) {
                                                            $columnType = strtolower($column['type']);
                                                        } elseif (!empty($comment) && is_string($comment)) {
                                                            $c = strtolower($comment);
                                                            if ($c === 'file') {
                                                                $columnType = 'file';
                                                            } elseif ($c === 'json') {
                                                                $columnType = 'json';
                                                            } elseif ($c === 'date') {
                                                                $columnType = 'date';
                                                            } elseif ($c === 'number' || $c === 'int' || $c === 'float') {
                                                                $columnType = 'number';
                                                            } else {
                                                                // fallback: по имени типа поля (если доступно)
                                                                $t = strtolower($column['type'] ?? '');
                                                                if (str_contains($t, 'int') || str_contains($t, 'float') || str_contains($t, 'decimal')) {
                                                                    $columnType = 'number';
                                                                } else {
                                                                    $columnType = 'text';
                                                                }
                                                            }
                                                        }
                                                    @endphp


                                                    {{-- Файлы: если определён тип file или старый fallback longtext+comment==='file' --}}
                                                    @if ($value && ($columnType === 'file' || ($columnType === 'longtext' && $comment === 'file')))
                                                        {{-- Вывод файлов как квадратиков с превью и кнопкой скачивания --}}
                                                        <div class="flex gap-2 flex-wrap">
                                                            @php
                                                                $files = is_string($value) ? json_decode($value, true) : $value;
                                                                $files = is_array($files) ? $files : [];
                                                            @endphp

                                                            @foreach ($files as $file)
                                                                @php
                                                                    $fileUrl = asset('storage/' . ltrim($file, '/'));
                                                                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?: '');
                                                                    $filename = basename($file);
                                                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                                                @endphp

                                                                <div class="relative group w-12 h-12">
                                                                    {{-- Обертка для открытия в новой вкладке --}}
                                                                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                                                                        @if ($isImage)
                                                                            <img src="{{ $fileUrl }}" alt="{{ $filename }}"
                                                                                class="w-full h-full object-cover rounded-lg border border-[#BFBFBF] cursor-pointer transition-opacity duration-150 group-hover:opacity-80">
                                                                        @else
                                                                            <div class="w-full h-full bg-gray-100 rounded-lg border border-[#BFBFBF] flex items-center justify-center text-xs font-bold text-[#060606]">
                                                                                {{ strtoupper($extension ?: 'FILE') }}
                                                                            </div>
                                                                        @endif
                                                                    </a>

                                                                    {{-- Overlay кнопка скачивания --}}
                                                                    <a href="{{ $fileUrl }}" download="{{ $filename }}"
                                                                        class="absolute -right-1 -bottom-1 transform translate-y-1/2 translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-150 bg-white rounded-full p-1 shadow-md border border-[#E5E7EB]"
                                                                        title="Скачать {{ $filename }}">
                                                                        <svg class="w-4 h-4 text-[#060606]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                                                                        </svg>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                    {{-- JSON/списки --}}
                                                    @elseif ($value && ($columnType === 'json' || ($columnType === 'longtext' && $comment === 'json')))
                                                        {{-- Вывод JSON как список --}}
                                                        @php
                                                            $jsonData = is_string($value) ? json_decode($value, true) : $value;
                                                            $jsonData = is_array($jsonData) ? $jsonData : [];
                                                        @endphp

                                                        <ul class="list-disc list-inside text-[#060606] max-w-[220px]">
                                                            @foreach ($jsonData as $item)
                                                                <li class="text-sm truncate">
                                                                    @if (is_string($item) || is_numeric($item))
                                                                        {{ $item }}
                                                                    @elseif (is_array($item))
                                                                        {{-- если массив — вывести значения через запятую или ключ:значение --}}
                                                                        @php
                                                                            $assoc = array_keys($item) !== range(0, count($item) - 1);
                                                                        @endphp
                                                                        @if ($assoc)
                                                                            @foreach ($item as $k => $v)
                                                                                <span class="inline-block mr-1 text-xs font-medium">{{ $k }}:</span><span class="text-xs">{{ is_scalar($v) ? $v : json_encode($v) }}</span>@if (!$loop->last), @endif
                                                                            @endforeach
                                                                        @else
                                                                            {{ implode(', ', array_map(function($v){ return is_scalar($v)? $v : json_encode($v); }, $item)) }}
                                                                        @endif
                                                                    @else
                                                                        {{ json_encode($item) }}
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>

                                                    @else
                                                        {{-- Обычный текст --}}
                                                        <span class="text-[#060606] truncate max-w-[150px]">
                                                            {{ $value ?? '' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach



                                            {{-- user --}}
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-[#565A5B]">Пользователь</span>
                                                <span class="text-[#060606] truncate max-w-[150px]">
                                                    {{ $employee->user->login}}
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-[#565A5B]">Роль</span>
                                                <span class="text-[#060606] truncate max-w-[150px]">
                                                    {{ $employee->user->role->description}}
                                                </span>
                                            </div>
                                            {{-- кнопка создания сотрудника --}}
                                            <a href="{{ route('employees.edit', [
                            'id' => $employee->id,
                            'back_url' => url()->full(),
                        ]) }}"
                                                class="w-full inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                                Редактировать сотрудника
                                            </a>
                                        </div>
                                    </details>

                                    {{-- должности --}}
                                    <details class="group bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg overflow-hidden">
                                        <!-- Заголовок аккордеона -->
                                        <summary
                                            class="flex items-center justify-between cursor-pointer p-4 hover:bg-[#A60644]/10 transition-colors duration-200">
                                            <h1 class="text-xl font-bold text-[#060606]">Должности</h1>
                                            <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                                </path>
                                            </svg>
                                        </summary>

                                        <!-- Содержимое аккордеона -->
                                        <div class="p-4 space-y-3 animate-fadeIn">
                                            <a href="{{ route('employee-positions.create', [
                            'id' => $employee->id,
                            'back_url' => url()->full(),
                        ]) }}"
                                                class="w-full inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4">
                                                    </path>
                                                </svg>
                                                Назначить должность
                                            </a>


                                            @foreach ($employee->employeePositions as $position)
                                                <div class="bg-white/50 rounded-lg border border-[#BFBFBF] p-4 mb-4 last:mb-0">
                                                    <div class="space-y-1.5 text-sm">
                                                        @php
                                                            $info = [
                                                                ['label' => 'Комиссариат', 'value' => $position->commissariat->name],
                                                                ['label' => 'Должность', 'value' => $position->position->name],
                                                                ['label' => 'Тип должности', 'value' => $position->position->positionType->name],
                                                                ['label' => 'Тип руководителя', 'value' => $position->position->ChiefType->name],
                                                                // ['label' => 'Ставка', 'value' => $position->getRateValueAttribute()],
                                                                ['label' => 'Самостоятельная', 'value' => $position->is_independent ? 'да' : 'нет'],
                                                                ['label' => 'Статус', 'value' => $position->getStatusNameAttribute()],
                                                            ];
                                                        @endphp

                                                        @foreach ($info as $item)
                                                            <div class="flex items-center justify-between">
                                                                <span class="font-medium text-[#060606]">{{ $item['label'] }}</span>
                                                                <span class="text-[#060606]">{{ $item['value'] }}</span>
                                                            </div>
                                                        @endforeach

                                                        <div class="flex gap-2 pt-2 mt-2 border-t border-[#BFBFBF]/30">
                                                            <a href="{{ route('employee-positions.edit', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                                                class="inline-flex items-center px-3 py-1.5 bg-[#A60644] text-white text-xs font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors shadow-sm">
                                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                </svg>
                                                                Редактировать
                                                            </a>
                                                            <form action="{{ route('employee-positions.delete', $position->id) }}" method="POST"
                                                                class="inline-block"
                                                                onsubmit="return confirm('Вы уверены, что хотите удалить должность?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="backUrl" value="{{ url()->full() }}">
                                                                <button type="submit"
                                                                    class="inline-flex items-center px-3 py-1.5 bg-[#060606] text-white text-xs font-medium rounded-lg hover:bg-[#060606]/80 transition-colors shadow-sm">
                                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                    Удалить
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
            </div>
        </div>
    </div>
@endsection

