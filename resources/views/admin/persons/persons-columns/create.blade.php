@extends('layouts.main')

@section('header-title')
    Создание колонок
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ? $backUrl : route('persons-columns.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
                </a>
            </div>
        </div>






        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('persons-columns.store') }}" method="post" class="space-y-8 max-w-4xl mx-auto">
                    @csrf

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">

                    <div class="border-t border-[#E5E5E5] pt-8">
                        <h3 class="text-lg font-medium text-[#060606] mb-5">
                            Настройки колонки
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Имя колонки -->
                            <div>
                                <label for="column_name" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Имя колонки (англ.) *
                                </label>
                                <input type="text" name="column_name" id="column_name" required pattern="[a-z0-9_]+"
                                    title="Только латинские буквы, цифры и подчёркивание"
                                    placeholder="title, price, phones_json, employee_photo" value="{{ old('column_name') }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                                                                      focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                                                                      outline-none transition-colors text-[#060606]">
                            </div>

                            <!-- Тип данных -->
                            <div>
                                <label for="column_type" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Тип данных *
                                </label>
                                <select name="column_type" id="column_type" required
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                                                                       focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                                                                       outline-none transition-colors text-[#060606]">
                                    <option value="" disabled selected>— Выберите тип —</option>

                                    <optgroup label="Числовые">
                                        <option value="integer" @selected(old('column_type') === 'integer')>INT (числа)
                                        </option>
                                        <option value="decimal" @selected(old('column_type') === 'decimal')>DECIMAL (числа с
                                            плавающей точкой)</option>
                                    </optgroup>

                                    <optgroup label="Строки и текст">
                                        <option value="string" @selected(old('column_type') === 'string')>VARCHAR (до 255
                                            символов)</option>
                                        <option value="text" @selected(old('column_type') === 'text')>TEXT (описание)
                                        </option>
                                        <option value="json" @selected(old('column_type') === 'json')>JSON (списки, массив
                                            данных)</option>
                                    </optgroup>

                                    <optgroup label="Дата и время">
                                        <option value="date" @selected(old('column_type') === 'date')>DATE (дата)</option>
                                        <option value="datetime" @selected(old('column_type') === 'datetime')>DATETIME
                                            (дата/время)</option>
                                    </optgroup>

                                    <optgroup label="Файлы (BLOB)">
                                        <option value="blob" @selected(old('column_type') === 'blob')>BLOB (до 64 кб)</option>
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Русское название / комментарий -->
                            <div class="md:col-span-2">
                                <label for="comment_ru" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Название поля на русском *
                                </label>
                                <input required type="text" name="comment_ru" id="comment_ru"
                                    placeholder="Фотография сотрудника, Список телефонов в формате JSON, Документы"
                                    value="{{ old('comment_ru') }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                                                                      focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                                                                      outline-none transition-colors text-[#060606]">
                            </div>

                            <!-- Значение по умолчанию -->
                            <div>
                                <label for="default" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Значение по умолчанию
                                </label>
                                <input type="text" name="default" id="default" placeholder="NULL, 0, 2026-01-01"
                                    value="{{ old('default') }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                                                                      focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                                                                      outline-none transition-colors text-[#060606]">
                            </div>

                        </div>
                    </div>

                    <div class="pt-8 flex justify-end">
                        <button type="submit"
                            class="group inline-flex items-center px-10 py-3.5 bg-[#A60644] text-white font-medium rounded-lg 
                                                                                                                           transition-all duration-200 hover:bg-[#8E0538] active:bg-[#7A0430] active:scale-[0.98] 
                                                                                                                           shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2.5 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Создать колонку
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection