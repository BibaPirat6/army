@extends('layouts.main')

@section('header-title')
    Добавить рабочий статус
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('work-statuses.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку статусов
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Добавить рабочий статус</h1>
            <p class="text-[#565A5B] mt-1">Создание нового рабочего статуса для системы</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('work-statuses.store') }}" method="post" class="space-y-6">
                    @csrf

                    <!-- Название на английском -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название (на английском)
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="Введите название на английском"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Название на русском -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название (на русском)
                        </label>
                        <input type="text" name="description" id="description" value="{{ old('description') }}" required
                            placeholder="Введите описание на русском"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Кнопка отправки -->
                    <div class="pt-4 flex justify-end">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Добавить статус
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
