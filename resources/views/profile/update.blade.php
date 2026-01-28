@extends('layouts.main')

@section('header-title')
    Редактирование профиля
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-2xl mx-auto bg-[#e7e1e1] rounded-2xl shadow-lg overflow-hidden p-6 md:p-8">
        <h1 class="text-2xl font-bold text-[#060606] text-center mb-8 pb-4 border-b border-[#BFBFBF]">Редактирование профиля
        </h1>

        <!-- Текущая информация -->
        <div class="mb-8 p-6 bg-[#060606]/5 rounded-xl border border-[#BFBFBF]">
            <h2 class="text-lg font-semibold text-[#565A5B] mb-4">Текущие данные</h2>

            <div class="space-y-3">
                <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                    <span class="font-bold text-[#565A5B] min-w-max">Логин</span>
                    <span class="text-[#060606]">{{ $employee->user?->login }}</span>

                    <span class="font-bold text-[#565A5B]">Роль</span>
                    <span class="text-[#060606]">{{ $employee->user?->role?->description }}</span>

                    <span class="font-bold text-[#565A5B]">ФИО</span>
                    <span class="text-[#060606]">
                        {{ $employee->person?->last_name }}
                        {{ $employee->person?->first_name }}
                        {{ $employee->person?->patronymic }}
                    </span>

                    <span class="font-bold text-[#565A5B]">Телефон</span>
                    <span class="text-[#060606]">{{ $employee->person?->phone }}</span>

                    <span class="font-bold text-[#565A5B]">Почта</span>
                    <span class="text-[#060606]">{{ $employee->person?->email }}</span>
                </div>
            </div>

            @if ($employee->person?->photo)
                <div class="mt-4 flex justify-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-[#565A5B] bg-[#060606]">
                        <img src="{{ asset('storage/' . $employee->person->photo) }}" alt="Фото профиля"
                            class="w-full h-full object-cover">
                    </div>
                </div>
            @endif
        </div>

        <!-- Форма редактирования -->
        <div class="bg-white/80 p-6 rounded-xl border border-[#BFBFBF]">
            <form action="{{ route('profile.update.post') }}" method="post" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Логин -->
                    <div class="md:col-span-2">
                        <label for="login" class="block text-sm font-medium text-[#565A5B] mb-2">Логин</label>
                        <input type="text" name="login" id="login" placeholder="Введите логин"
                            value="{{ old('login', $employee->user?->login) }}"
                            class="w-full px-4 py-3 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- Фамилия -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-[#565A5B] mb-2">Фамилия</label>
                        <input type="text" name="last_name" id="last_name" placeholder="Введите фамилию"
                            value="{{ old('last_name', $employee->person?->last_name) }}"
                            class="w-full px-4 py-3 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- Имя -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-[#565A5B] mb-2">Имя</label>
                        <input type="text" name="first_name" id="first_name" placeholder="Введите имя"
                            value="{{ old('first_name', $employee->person?->first_name) }}"
                            class="w-full px-4 py-3 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- Отчество -->
                    <div>
                        <label for="patronymic" class="block text-sm font-medium text-[#565A5B] mb-2">Отчество</label>
                        <input type="text" name="patronymic" id="patronymic" placeholder="Введите отчество"
                            value="{{ old('patronymic', $employee->person?->patronymic) }}"
                            class="w-full px-4 py-3 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- Почта -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#565A5B] mb-2">Почта</label>
                        <input type="email" name="email" id="email" placeholder="Введите почту"
                            value="{{ old('email', $employee->person?->email) }}"
                            class="w-full px-4 py-3 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- Телефон -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-[#565A5B] mb-2">Телефон</label>
                        <input type="tel" name="phone" id="phone" placeholder="Введите телефон"
                            value="{{ old('phone', $employee->person?->phone) }}"
                            class="w-full px-4 py-3 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                    </div>

                    <!-- Фото -->
                    <div class="md:col-span-2">
                        <label for="photo" class="block text-sm font-medium text-[#565A5B] mb-2">Фото</label>
                        <input type="file" name="photo" id="photo" accept="image/*"
                            class="w-full px-4 py-3 bg-[#e7e1e1] border border-[#BFBFBF] rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#A60644] file:text-white file:font-medium file:cursor-pointer hover:file:bg-[#A60644]/80 transition-colors">
                    </div>
                </div>

                <!-- Кнопка отправки -->
                <div class="pt-4 flex justify-center">
                    <button type="submit"
                        class="group inline-flex items-center px-8 py-3 bg-[#A60644] text-white font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/80 active:bg-[#A60644]/60 active:scale-[0.98] shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
