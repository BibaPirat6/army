@extends('layouts.main')

@section('header-title')
    Добавление отделения
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('divisions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Добавление отделения</h1>
            <p class="text-[#565A5B] mt-1">Заполните все необходимые поля для создания нового отделения</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('divisions.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Название отделения -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Название отделения *
                        </label>
                        <input type="text" name="name" id="name" placeholder="Название отделения"
                            value="{{ old('name') }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>


                    <!-- отдел -->
                    <div>
                        <label for="department_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Отдел *
                        </label>
                        <select name="department_id" id="department_id" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                            <option value="" selected>Не выбран</option>
                        </select>
                    </div>

                    <!-- комиссариат -->
                    <div>
                        <label for="commissariat_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Комиссариат *
                        </label>
                        <select name="commissariat_id" id="commissariat_id" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @foreach ($commissariats as $commissariat)
                                <option value="{{ $commissariat->id }}"
                                    {{ old('commissariat_id') == $commissariat->id ? 'selected' : '' }}>
                                    {{ $commissariat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>



                    <!-- Кнопка отправки -->
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Создать отделение
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
