@extends('layouts.main')

@section('header-title')
    Пользователь {{ $user->login }}
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $decodedBackUrl ? $decodedBackUrl : route('users.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Пользователь {{ $user->login }}</h1>
            <p class="text-[#565A5B] mt-1">Редактирование данных пользователя</p>
        </div>

        <!-- Форма -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('users.update', $user->id) }}" method="post" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="employeeId" value="{{ $employeeId }}">
                    <input type="hidden" name="decodedBackUrl" value="{{ $decodedBackUrl }}">

                    <!-- Логин -->
                    <div>
                        <label for="login" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Логин *
                        </label>
                        <input type="text" name="login" id="login" placeholder="Введите логин"
                            value="{{ old('login', $user->login) }}" required
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                        @error('login')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Пароль -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Пароль *
                        </label>
                        <input type="password" name="password" id="password"
                            placeholder="Введите новый пароль (оставьте пустым, чтобы не изменять)" value=""
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Роль -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-[#565A5B] mb-2">
                            Роль
                        </label>
                        <select name="role" id="role"
                            class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @if (old('role', $user->role?->name) == $role->name) selected @endif>
                                    {{ $role->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Кнопка отправки -->
                    <div class="pt-6 flex justify-end">
                        <button type="submit"
                            class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Обновить пользователя
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
