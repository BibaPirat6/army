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

        <div class="space-y-4">
            <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
                <span class="font-bold text-[#565A5B] min-w-max">Логин</span>
                <span class="text-[#060606] break-words">{{ $employee->user?->login }}</span>

                <span class="font-bold text-[#565A5B]">Роль</span>
                <span class="text-[#060606] break-words">{{ $employee->user?->role?->description }}</span>
            </div>
        </div>
    </div>
@endsection
