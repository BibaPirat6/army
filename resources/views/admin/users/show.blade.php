@extends('layouts.main')

@section('header-title')
    Пользователь {{ $user->login }}
@endsection

@section('content')
    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('users.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">{{ $user->login }}</h1>
            <p class="text-[#565A5B] mt-1">Детали пользователя</p>
        </div>

        <!-- Карточка с информацией -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">ID</span>
                        <span class="text-[#060606]">{{ $user->id }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Логин</span>
                        <span class="text-[#060606]">{{ $user->login }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Роль</span>
                        <span class="text-[#060606]">{{ $user->role->description }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Создан</span>
                        <span
                            class="text-[#060606]">{{ \Carbon\Carbon::parse($user->created_at)->format('d.m.Y H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Обновлен</span>
                        <span
                            class="text-[#060606]">{{ $user->updated_at ? \Carbon\Carbon::parse($user->updated_at)->format('d.m.Y H:i') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
