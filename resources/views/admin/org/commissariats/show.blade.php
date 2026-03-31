@extends('layouts.main')

@section('header-title')
    {{ $commissariat['name'] }}
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
        {{-- кнопка назад --}}
        <div class="flex items-center mb-4">
            <a href="{{ $backUrl ?? route('commissariats.index') }}"
                class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Назад
            </a>
        </div>

        {{-- данные --}}
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">

                <div class="space-y-4">
                    {{-- комиссариат --}}
                    <details class="group bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg overflow-hidden">
                        <!-- Заголовок аккордеона -->
                        <summary
                            class="flex items-center justify-between cursor-pointer p-4 hover:bg-[#A60644]/10 transition-colors duration-200">
                            <h1 class="text-xl font-bold text-[#060606]">Детали комиссариата</h1>
                            <svg class="w-5 h-5 text-[#565A5B] group-open:rotate-180 transition-transform duration-300"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </summary>

                        <div class="p-4 space-y-3 animate-fadeIn">
                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">ID</span>
                                <span class="text-[#060606]">{{ $commissariat['id'] }}</span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">Начальник</span>

                                <span class="text-[#060606]">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ optional($commissariat->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                    </span>
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                                <span class="font-medium text-[#565A5B]">Координаты</span>
                                <span class="text-[#060606]">X: {{ $commissariat['longitude'] ?? '*' }} Y:
                                    {{ $commissariat['latitude'] ?? '*' }}</span>
                            </div>

                            <div>
                                <a href="{{ route('commissariats.edit', [
        'id' => $commissariat->id,
        'back_url' => url()->full(),
    ]) }}" class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    Редактировать комиссариат
                                </a>
                                <form action="{{ route('commissariats.delete', $commissariat->id) }}" method="POST"
                                    class="inline-block mt-0.5"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить комиссариат \'{{ $commissariat->name }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="backUrl"
                                        value="{{ $backUrl ?? route('commissariats.index') }}">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        Удалить комиссариат
                                    </button>
                                </form>
                            </div>
                        </div>
                    </details>

                    {{-- сотрудник и должности --}}
                    @php
                        $chief = $commissariat->getChiefAttribute();
                    @endphp
                    @if ($chief)
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
                                                        $value = $chief->person->{$columnName} ?? null;
                                                        $columnType = $column['type'] ?? 'text'; // предполагаем, что тип хранится в $column['type']
                                                    @endphp

                                                    @if ($columnType === 'file' && $value)
                                                        {{-- Вывод файлов --}}
                                                        <div class="flex gap-2 flex-wrap">
                                                            @php
                                                                $files = is_string($value) ? json_decode($value, true) : $value;
                                                                $files = is_array($files) ? $files : [];
                                                            @endphp

                                                            @foreach ($files as $file)
                                                                @php
                                                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                                                    $filename = basename($file);
                                                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                                                @endphp

                                                                @if ($isImage)
                                                                    {{-- Картинка --}}
                                                                    <div class="relative group" title="{{ $filename }}">
                                                                        <img src="{{ asset('storage/' . $file) }}" alt="{{ $filename }}"
                                                                            class="w-12 h-12 object-cover rounded-lg border border-[#BFBFBF] cursor-pointer hover:opacity-80 transition-opacity"
                                                                            onclick="window.open('{{ asset('storage/' . $file) }}', '_blank')">
                                                                    </div>
                                                                @else
                                                                    {{-- Другой файл --}}
                                                                    <div class="w-12 h-12 bg-gray-200 rounded-lg border border-[#BFBFBF] flex items-center justify-center text-xs font-bold text-[#060606] hover:bg-gray-300 transition-colors cursor-pointer"
                                                                        title="{{ $filename }}"
                                                                        onclick="window.open('{{ asset('storage/' . $file) }}', '_blank')">
                                                                        {{ strtoupper($extension) }}
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>

                                                    @elseif ($columnType === 'json' && $value)
                                                        {{-- Вывод JSON как список --}}
                                                        @php
                                                            $jsonData = is_string($value) ? json_decode($value, true) : $value;
                                                            $jsonData = is_array($jsonData) ? $jsonData : [];
                                                        @endphp

                                                        <ul class="list-disc list-inside text-[#060606] max-w-[200px] truncate">
                                                            @foreach ($jsonData as $item)
                                                                <li class="text-sm truncate">{{ is_string($item) ? $item : json_encode($item) }}</li>
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
                                                    {{ $commissariat->getChiefAttribute()->user->login}}
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-[#565A5B]">Роль</span>
                                                <span class="text-[#060606] truncate max-w-[150px]">
                                                    {{ $commissariat->getChiefAttribute()->user->role->description}}
                                                </span>
                                            </div>
                                            {{-- кнопка создания сотрудника --}}
                                            <a href="{{ route('employees.edit', [
                            'id' => $commissariat->getChiefAttribute()->id,
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
                            'id' => $commissariat->getChiefAttribute()->id,
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


                                            @foreach ($commissariat->getChiefAttribute()->employeePositions as $position)
                                                <div class="bg-white/50 rounded-lg border border-[#BFBFBF] p-4 mb-4 last:mb-0">
                                                    <div class="space-y-1.5 text-sm">
                                                        @php
                                                            $info = [
                                                                ['label' => 'Комиссариат', 'value' => $position->commissariat->name],
                                                                ['label' => 'Должность', 'value' => $position->position->name],
                                                                ['label' => 'Тип должности', 'value' => $position->position->positionType->name],
                                                                ['label' => 'Тип руководителя', 'value' => $position->position->ChiefType->name],
                                                                ['label' => 'Ставка', 'value' => $position->getRateValueAttribute()],
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
                                                            <a href="{{ route('employee-positions.edit', ['id' => $commissariat->getChiefAttribute()->id, 'back_url' => url()->full()]) }}"
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
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection