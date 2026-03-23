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
                                    Имя колонки *
                                </label>
                                <input type="text" name="column_name" id="column_name" required
                                    title="Только латинские буквы, цифры и подчёркивание"
                                    placeholder="Пол, возраст, паспорт" value="{{ old('column_name') }}"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                                                                                                                                          focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                                                                                                                                          outline-none transition-colors text-[#060606]">
                            </div>

                            {{-- типы --}}
                            <div>
                                <label for="column_type" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Тип данных *
                                </label>
                                <select name="column_type" id="column_type" required onchange="toggleDefaultField(this)"
                                    class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                               outline-none transition-colors text-[#060606]">
                                    <option value="" disabled selected>— Выберите тип —</option>

                                    <option value="integer" @selected(old('column_type') === 'integer')>int (целые)
                                    </option>
                                    <option value="decimal" @selected(old('column_type') === 'decimal')>decimal (числа с
                                        плавающей точкой)</option>

                                    <option value="varchar" @selected(old('column_type') === 'varchar')>varchar (описание)
                                    </option>
                                    <option value="json" @selected(old('column_type') === 'json')>json (списки, массив
                                        данных)</option>

                                    <option value="date" @selected(old('column_type') === 'date')>date (дата 2020-01-01)
                                    </option>

                                    <option value="file" @selected(old('column_type') === 'file')>файлы (фотки, word, pdf)
                                    </option>
                                </select>
                            </div>

                            <!-- Значение по умолчанию -->
                            <div id="default-field-container">
                                <label for="default" class="block text-sm font-medium text-[#565A5B] mb-2">
                                    Значение по умолчанию
                                </label>
                                <input type="text" name="default" id="default" placeholder="мужской пол, 0, 2026-01-01"
                                    value="{{ old('default') }}" class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg 
                               focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] 
                               outline-none transition-colors text-[#060606]">
                            </div>


                            {{-- nullable --}}
                            <div class="mb-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="nullable" value="1" {{ old('nullable') ? 'checked' : '' }}
                                        class="w-4 h-4 text-[#A60644] bg-white border-[#BFBFBF] rounded 
                          focus:ring-[#A60644] focus:ring-2">
                                    <span class="ml-2 text-sm font-medium text-[#565A5B]">
                                        Разрешить NULL (пустое значение)
                                    </span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">
                                    Если отмечено — поле может быть пустым. Если не отмечено — поле обязательно для
                                    заполнения.
                                </p>
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


<script>
    function toggleDefaultField(select) {
        const selectedType = select.value;
        const container = document.getElementById('default-field-container');
        const defaultInput = document.getElementById('default');

        const noDefaultTypes = ['json', 'file'];

        if (noDefaultTypes.includes(selectedType)) {
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            let tooltip = document.getElementById('default-tooltip');
            if (!tooltip) {
                tooltip = document.createElement('p');
                tooltip.id = 'default-tooltip';
                tooltip.className = 'text-xs text-gray-500 mt-1';
                tooltip.textContent = 'Для этого типа данных нельзя указать значение по умолчанию';
                container.appendChild(tooltip);
            }



        } else {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';

            const tooltip = document.getElementById('default-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('column_type');
        if (select.value) {
            toggleDefaultField(select);
        }
    });
</script>