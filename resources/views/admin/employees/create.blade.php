@extends('layouts.main')

@section('header-title')
    Создание сотрудника
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('employees.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Создание сотрудника</h1>
            <p class="text-[#565A5B] mt-1">Заполните все необходимые поля для создания нового сотрудника</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('employees.store') }}" method="post" class="space-y-6">
                    @csrf

                    <!-- Выбор пользователя -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Выберите пользователя
                        </label>
                        <select name="user_id" id="user_id"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            <option value="">Не выбирать</option>
                            @if ($users && count($users) > 0)
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->login }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>Нет свободных пользователей</option>
                            @endif
                        </select>
                        @error('user_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Выбор персональных данных -->
                    <div>
                        <label for="person_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Выберите персональные данные сотрудника
                        </label>
                        <select name="person_id" id="person_id"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            <option value="">Не выбирать</option>
                            @if ($persons && count($persons) > 0)
                                @foreach ($persons as $person)
                                    <option value="{{ $person->id }}">
                                        {{ $person->last_name }} {{ $person->first_name }} {{ $person->phone }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>Нет свободных персональных данных</option>
                            @endif
                        </select>
                        @error('person_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Рабочий статус -->
                    <div>
                        <label for="work_status" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Рабочий статус *
                        </label>
                        <select name="work_status" id="work_status" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" @if (old('work_status', $status->name) == 'inactive') selected @endif>
                                    {{ $status->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('work_status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                            Создать сотрудника
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection
