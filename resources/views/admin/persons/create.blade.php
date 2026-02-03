@extends('layouts.main')

@section('header-title')
    Создание Персональных данных
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ? $backUrl : route('persons.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Создание персональных данных</h1>
            <p class="text-[#565A5B] mt-1">Заполните все необходимые поля для создания новых персональных данных</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('persons.store') }}" method="post" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <input type="hidden" name="backUrl" value="{{ $backUrl }}">
                    <input type="hidden" name="employeeId" value="{{ $employeeId }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Фамилия -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Фамилия *
                            </label>
                            <input type="text" name="last_name" id="last_name" placeholder="Введите фамилию"
                                value="{{ old('last_name') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">

                        </div>

                        <!-- Имя -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Имя *
                            </label>
                            <input type="text" name="first_name" id="first_name" placeholder="Введите имя"
                                value="{{ old('first_name') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                        </div>

                        <!-- Отчество -->
                        <div class="md:col-span-2">
                            <label for="patronymic" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Отчество
                            </label>
                            <input type="text" name="patronymic" id="patronymic" placeholder="Введите отчество"
                                value="{{ old('patronymic') }}"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                        </div>

                        <!-- Почта -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-[#565A5B]">
                                Почты
                            </label>

                            <div id="emails-wrapper" class="space-y-2">
                            </div>

                            <button type="button" onclick="addEmail()" class="text-sm text-[#A60644] mt-2">
                                + Добавить почту
                            </button>
                        </div>

                        <!-- Телефон -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-[#565A5B]">
                                Телефоны
                            </label>

                            <div id="phones-wrapper" class="space-y-2">
                            </div>

                            <button type="button" onclick="addPhone()" class="text-sm text-[#A60644] mt-2">
                                + Добавить телефон
                            </button>
                        </div>


                        <!-- Фото -->
                        <div class="md:col-span-2">
                            <label for="photo" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Фото
                            </label>
                            <input type="file" name="photo" id="photo" accept="image/*"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#A60644] file:text-white file:font-medium file:cursor-pointer hover:file:bg-[#A60644]/80 transition-colors text-[#060606]">

                        </div>
                    </div>

                    <!-- Кнопка отправки -->
                    <div class="pt-6 flex justify-end">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Создать персональные данные
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


<script>
    function addEmail() {
        const wrapper = document.getElementById('emails-wrapper');
        wrapper.appendChild(createRow('email', 'emails[]', 'Введите почту'));
    }

    function addPhone() {
        const wrapper = document.getElementById('phones-wrapper');
        wrapper.appendChild(createRow('tel', 'phones[]', 'Введите телефон'));
    }

    function createRow(type, name, placeholder) {
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center';

        div.innerHTML = `
        <input type="${type}" name="${name}" placeholder="${placeholder}"
            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg">
        <button type="button" onclick="removeRow(this)"
            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
            ✕
        </button>
    `;

        return div;
    }

    function removeRow(button) {
        button.parentElement.remove();
    }
</script>
