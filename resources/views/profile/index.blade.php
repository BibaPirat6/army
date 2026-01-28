@extends('layouts.main')

@section('header-title')
    Профиль
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <div class="max-w-md mx-auto bg-[#e7e1e1] rounded-2xl shadow-lg overflow-hidden p-6 md:p-8 mt-10">
        <h1 class="text-2xl font-bold text-[#060606] text-center mb-8 pb-4 border-b border-[#BFBFBF]">Мой профиль</h1>

        @if ($employee->person?->photo)
            <div class="flex justify-center mb-6">
                <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-[#565A5B] bg-[#060606]">
                    <img src="{{ asset('storage/' . $employee->person->photo) }}" alt="Фото профиля"
                        class="object-cover w-full h-full">
                </div>
            </div>
        @endif

        <div class="space-y-4">
            <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
                <span class="font-bold text-[#565A5B] min-w-max">Логин</span>
                <span class="text-[#060606] break-words">{{ $employee->user?->login }}</span>

                <span class="font-bold text-[#565A5B]">Роль</span>
                <span class="text-[#060606] break-words">{{ $employee->user?->role?->description }}</span>

                <span class="font-bold text-[#565A5B]">ФИО</span>
                <span class="text-[#060606] break-words">
                    {{ $employee->person?->last_name }}
                    {{ $employee->person?->first_name }}
                    {{ $employee->person?->patronymic }}
                </span>

                <span class="font-bold text-[#565A5B]">Телефон</span>
                <span class="text-[#060606] break-words">{{ $employee->person?->phone }}</span>

                <span class="font-bold text-[#565A5B]">Почта</span>
                <span class="text-[#060606] break-words">{{ $employee->person?->email }}</span>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-[#BFBFBF] flex justify-center">
            <a href="{{ route('profile.update.index') }}"
                class="group inline-flex items-center px-4 py-2 text-[#A60644] font-medium rounded-lg transition-all duration-200 hover:bg-[#A60644]/5 active:scale-[0.98]">
                <svg class="w-4 h-4 mr-2 transition-transform group-hover:translate-x-1" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
                Редактировать профиль
            </a>
        </div>
    </div>
@endsection
