@extends('layouts.main')

@section('header-title')
    {{ $employee->getFullNameAttribute() }}
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-4xl p-6 mx-auto">
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
            <p class="text-[#565A5B] mt-1">Просмотр и управление данными сотрудника</p>
        </div>

        <!-- Основная карточка -->
        <div class="rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <!-- Заголовок с именем -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#BFBFBF]/50">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-[#A60644]/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-[#060606]">
                            {{ $employee->getFullNameAttribute() }}
                        </h2>
                    </div>
                    <a href="{{ route('employees.edit', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                        class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Редактировать
                    </a>
                </div>

                <!-- Данные сотрудника (аккордеон) -->
                <details class="group rounded-xl border border-[#BFBFBF] overflow-hidden mb-4" open>
                    <summary
                        class="flex items-center justify-between cursor-pointer p-4 bg-gradient-to-r from-[#A60644]/5 to-transparent hover:bg-[#A60644]/10 transition-colors duration-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-4 0h4" />
                            </svg>
                            <h3 class="text-lg font-bold text-[#060606]">Личные данные</h3>
                        </div>
                        <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>

                    <div class="p-4 space-y-0 divide-y divide-[#BFBFBF]/20">
                        @foreach ($columns as $column)
                            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                <span class="font-medium text-[#565A5B] w-1/3">{{ $column['name'] }}</span>

                                <div class="w-2/3">
                                    @php
                                        $columnName = $column['name'];
                                        $type = $column['type'] ?? '';
                                        $comment = $column['comment'] ?? null;
                                        $value = $employee->person->{$columnName} ?? null;
                                        
                                        $normalizedType = preg_replace('/\(.*\)/', '', $type);
                                        
                                        $isFile = $comment === 'file';
                                        $isJson = $comment === 'json';
                                        $isBoolean = !$isFile && !$isJson && (str_contains($type, 'tinyint(1)') || $type === 'boolean');
                                        $isDate = !$isFile && !$isJson && !$isBoolean && str_contains($type, 'date');
                                        $isDecimal = !$isFile && !$isJson && !$isBoolean && !$isDate && (
                                            str_contains($normalizedType, 'decimal') ||
                                            str_contains($normalizedType, 'float') ||
                                            str_contains($normalizedType, 'double')
                                        );
                                        $isInteger = !$isFile && !$isJson && !$isBoolean && !$isDate && !$isDecimal &&
                                            str_contains($normalizedType, 'int');
                                    @endphp

                                    {{-- ФАЙЛЫ --}}
                                    @if ($isFile && $value)
                                        <div class="flex gap-2 flex-wrap">
                                            @php
                                                $files = is_string($value) ? json_decode($value, true) : $value;
                                                $files = is_array($files) ? $files : [];
                                            @endphp

                                            @forelse ($files as $file)
                                                @php
                                                    $fileUrl = asset('storage/' . ltrim($file, '/'));
                                                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?: '');
                                                    $filename = basename($file);
                                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                                @endphp

                                                <div class="relative group w-12 h-12">
                                                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer"
                                                        class="block w-full h-full">
                                                        @if ($isImage)
                                                            <img src="{{ $fileUrl }}" alt="{{ $filename }}"
                                                                class="w-full h-full object-cover rounded-lg border border-[#BFBFBF] cursor-pointer transition-opacity duration-150 group-hover:opacity-80">
                                                        @else
                                                            <div class="w-full h-full bg-gray-100 rounded-lg border border-[#BFBFBF] flex items-center justify-center text-xs font-bold text-[#060606]">
                                                                {{ strtoupper($extension ?: 'FILE') }}
                                                            </div>
                                                        @endif
                                                    </a>
                                                    <a href="{{ $fileUrl }}" download="{{ $filename }}"
                                                        class="absolute -right-1 -bottom-1 transform translate-y-1/2 translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-150 bg-white rounded-full p-1 shadow-md border border-[#E5E7EB]"
                                                        title="Скачать {{ $filename }}">
                                                        <svg class="w-3 h-3 text-[#060606]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            @empty
                                                <span class="text-gray-400 italic text-sm">Нет файлов</span>
                                            @endforelse
                                        </div>

                                    {{-- JSON СПИСКИ --}}
                                    @elseif ($isJson && $value)
                                        @php
                                            $jsonData = is_string($value) ? json_decode($value, true) : $value;
                                            $jsonData = is_array($jsonData) ? $jsonData : [];
                                        @endphp

                                        @if(count($jsonData) > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($jsonData as $item)
                                                    <span class="inline-block px-2 py-1 bg-[#A60644]/10 text-[#A60644] rounded-lg text-xs">
                                                        {{ $item }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic text-sm">Нет данных</span>
                                        @endif

                                    {{-- BOOLEAN --}}
                                    @elseif ($isBoolean)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $value == 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $value == 1 ? 'Да' : 'Нет' }}
                                        </span>

                                    {{-- DATE --}}
                                    @elseif ($isDate && $value)
                                        <span class="text-[#060606] text-sm">
                                            @php
                                                try {
                                                    echo \Carbon\Carbon::parse($value)->format('d.m.Y');
                                                } catch (\Exception $e) {
                                                    echo $value;
                                                }
                                            @endphp
                                        </span>

                                    {{-- DECIMAL / FLOAT --}}
                                    @elseif ($isDecimal && $value !== null && $value !== '')
                                        <span class="text-[#060606] text-sm font-mono">
                                            {{ number_format((float) $value, 2, '.', ' ') }}
                                        </span>

                                    {{-- INTEGER --}}
                                    @elseif ($isInteger && $value !== null && $value !== '')
                                        <span class="text-[#060606] text-sm font-mono">
                                            {{ number_format((int) $value, 0, '.', ' ') }}
                                        </span>

                                    {{-- TEXTAREA / ОБЫЧНЫЙ ТЕКСТ --}}
                                    @elseif ($value !== null && $value !== '')
                                        @if (strlen($value) > 100)
                                            <div class="relative group">
                                                <span class="text-[#060606] text-sm truncate block max-w-[250px]">{{ Str::limit($value, 80) }}</span>
                                                <div class="absolute z-10 hidden group-hover:block bg-gray-800 text-white text-xs rounded-lg p-2 -mt-1 left-0 top-full whitespace-normal break-words max-w-md shadow-lg">
                                                    {{ $value }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-[#060606] text-sm break-words">{{ $value }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic text-sm">Не указано</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Учетные данные -->
                        <div class="flex items-center justify-between py-3">
                            <span class="font-medium text-[#565A5B] w-1/3">Логин</span>
                            <span class="text-[#060606] text-sm w-2/3">{{ $employee->user->login }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="font-medium text-[#565A5B] w-1/3">Роль</span>
                            <span class="text-[#060606] text-sm w-2/3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    {{ $employee->user->role->description }}
                                </span>
                            </span>
                        </div>
                    </div>
                </details>

                <!-- Должности (аккордеон) -->
                <details class="group bg-white rounded-xl border border-[#BFBFBF] overflow-hidden">
                    <summary
                        class="flex items-center justify-between cursor-pointer p-4 bg-gradient-to-r from-[#A60644]/5 to-transparent hover:bg-[#A60644]/10 transition-colors duration-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h3 class="text-lg font-bold text-[#060606]">Должности</h3>
                            <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-[#A60644]/20 text-[#A60644]">
                                {{ $employee->employeePositions->count() }}
                            </span>
                        </div>
                        <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </summary>

                    <div class="p-4">
                        @forelse ($employee->employeePositions as $position)
                            <div class="bg-gray-50 rounded-xl border border-[#BFBFBF]/30 p-4 mb-3 last:mb-0 hover:shadow-md transition-shadow duration-200">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-[#565A5B] text-xs uppercase tracking-wide">Должность</span>
                                        <p class="font-semibold text-[#060606]">{{ $position->position->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-[#565A5B] text-xs uppercase tracking-wide">Ставка</span>
                                        <p class="font-semibold text-[#060606]">{{ number_format($position->rate, 2) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-[#565A5B] text-xs uppercase tracking-wide">Комиссариат</span>
                                        <p class="text-[#060606]">{{ $position->commissariat->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-[#565A5B] text-xs uppercase tracking-wide">Статус</span>
                                        <p class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            style="background-color: {{ $position->employeePositionStatus->color }}20; color: {{ $position->employeePositionStatus->color }}">
                                            {{ $position->employeePositionStatus->name }}
                                        </p>
                                    </div>
                                    @if($position->department)
                                    <div>
                                        <span class="text-[#565A5B] text-xs uppercase tracking-wide">Отдел</span>
                                        <p class="text-[#060606]">{{ $position->department->name }}</p>
                                    </div>
                                    @endif
                                    @if($position->division)
                                    <div>
                                        <span class="text-[#565A5B] text-xs uppercase tracking-wide">Отделение</span>
                                        <p class="text-[#060606]">{{ $position->division->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-[#BFBFBF]">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p class="text-[#565A5B] text-sm mb-3">Нет назначенных должностей</p>
                                <a href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                    class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Назначить должность
                                </a>
                            </div>
                        @endforelse

                        @if($employee->employeePositions->count() > 0)
                            <div class="mt-4 text-center pt-3 border-t border-[#BFBFBF]/20">
                                <a href="{{ route('employee-positions.create', ['id' => $employee->id, 'back_url' => url()->full()]) }}"
                                    class="inline-flex items-center text-[#A60644] text-sm font-medium hover:underline">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Добавить еще должность
                                </a>
                            </div>
                        @endif
                    </div>
                </details>
            </div>
        </div>
    </div>
@endsection