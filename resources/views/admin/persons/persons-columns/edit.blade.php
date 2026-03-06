@extends('layouts.main')

@section('header-title')
    Редактирование колонки
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
                <form action="{{ route("persons-columns.update",[
                    "id"=>$column["name"]
                ]) }}" method="post" class="space-y-8 max-w-4xl mx-auto">
                    @csrf
                    @method("PUT")

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">



                    <div class="border-t border-[#E5E5E5] pt-8">
                        <h3 class="text-lg font-medium text-[#060606] mb-5">
                            Настройки колонки
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Имя колонки -->
                            <div>
                                <label for="column_name" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Имя колонки *
                                </label>
                                <input type="text" name="column_name" id="column_name" required
                                    placeholder="title, price, status, created_at"
                                    value="{{ old('column_name', $column["name"]) }}" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                          focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                          outline-none transition-colors text-[#060606]">
                            </div>

                               <div>
                                    <label for="column_type" class="block text-sm font-medium text-[#565A5B] mb-2">
                                        Тип данных *
                                    </label>
                                    <select name="column_type" id="column_type" required class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                outline-none transition-colors text-[#060606]">

                                        <option value="" disabled>— Выберите тип —</option>

                                        <optgroup label="Числовые">
                                            <option value="int" {{ $column['data_type'] === 'int' ? 'selected' : '' }}>INT (целое
                                                число)</option>
                                            <option value="decimal" {{ $column['data_type'] === 'decimal' ? 'selected' : '' }}>
                                                DECIMAL (числа с плавающей точкой)</option>
                                        </optgroup>

                                        <optgroup label="Строки / Текст">
                                            <option value="varchar" {{ $column['data_type'] === 'varchar' ? 'selected' : '' }}>
                                                VARCHAR (до 255 символов)</option>
                                            <option value="text" {{ $column['data_type'] === 'text' ? 'selected' : '' }}>TEXT
                                                (комментарий, адрес, примечание)</option>
                                        </optgroup>

                                        <optgroup label="Дата и время">
                                            <option value="date" {{ $column['data_type'] === 'date' ? 'selected' : '' }}>DATE
                                                (2025-12-31)</option>
                                            <option value="datetime" {{ $column['data_type'] === 'datetime' ? 'selected' : '' }}>
                                                DATETIME (2025-12-31 23:59:59)</option>
                                        </optgroup>

                                        <optgroup label="Файлы / Бинарные">
                                            <option value="file" {{ $column["comment"] === "file" ? 'selected' : '' }}>
                                                Файл / Фото / Документ (PDF, Word, Excel, изображение)
                                            </option>
                                        </optgroup>

                                        <optgroup label="Списки / Структурированные">
                                            <option value="json" {{ $column["comment"] === "json" ? 'selected' : '' }}>
                                                JSON (послужной список, места работы, массив объектов)
                                            </option>
                                        </optgroup>
                                    </select>
                                </div>



                                <!-- Default -->
                                <div>
                                    <label for="default" class="block text-sm font-medium text-[#565A5B] mb-2">
                                        Значение по умолчанию
                                    </label>
                                    <input type="text" name="default" id="default" placeholder="null, 0, 1"
                                        value="{{ old('default', $column["default"]) }}" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                          focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                          outline-none transition-colors text-[#060606]">
                                </div>

                                <!-- Чекбоксы -->
                                <div class="md:col-span-2 mt-2">
                                    <div class="flex flex-wrap gap-6 text-sm text-[#565A5B]">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="nullable" value="1" class="mr-2 accent-[#A60644]"
                                                {{ $column["nullable"] ? "checked" : "" }}>
                                            Разрешить NULL
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>



                        <!-- Кнопка -->
                        <div class="pt-8 flex justify-end">
                            <button type="submit" class="group inline-flex items-center px-10 py-3.5 bg-[#A60644] text-white font-medium rounded-lg 
                                                                   transition-all duration-200 hover:bg-[#8E0538] active:bg-[#7A0430] active:scale-[0.98] 
                                                                   shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2.5 transition-transform group-hover:scale-110" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Изменить колонку
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection