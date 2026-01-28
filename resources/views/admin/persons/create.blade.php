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
                                Фамилия
                            </label>
                            <input type="text" name="last_name" id="last_name" placeholder="Введите фамилию"
                                value="{{ old('last_name') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @error('last_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Имя -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Имя
                            </label>
                            <input type="text" name="first_name" id="first_name" placeholder="Введите имя"
                                value="{{ old('first_name') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @error('first_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Отчество -->
                        <div class="md:col-span-2">
                            <label for="patronymic" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Отчество
                            </label>
                            <input type="text" name="patronymic" id="patronymic" placeholder="Введите отчество"
                                value="{{ old('patronymic') }}"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @error('patronymic')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Почта -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Почта
                            </label>
                            <input type="email" name="email" id="email" placeholder="Введите почту"
                                value="{{ old('email') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Телефон -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Телефон
                            </label>
                            <input type="tel" name="phone" id="phone" placeholder="Введите телефон"
                                value="{{ old('phone') }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Фото -->
                        <div class="md:col-span-2">
                            <label for="photo" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Фото
                            </label>
                            <input type="file" name="photo" id="photo" accept="image/*"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#A60644] file:text-white file:font-medium file:cursor-pointer hover:file:bg-[#A60644]/80 transition-colors text-[#060606]">
                            @error('photo')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
