@extends('layouts.main')

@section('header-title')
    Персона {{ $person->last_name }} {{ $person->first_name }}
@endsection

@section('content')
    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('persons.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">{{ $person->last_name }} {{ $person->first_name }}
                {{ $person->patronymic ?? '' }}</h1>
            <p class="text-[#565A5B] mt-1">Детали персоны</p>
        </div>

        <!-- Карточка с информацией -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">ID</span>
                        <span class="text-[#060606]">{{ $person->id }}</span>
                    </div>

                    <div class="flex items-start justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Телефон</span>
                        <div class="text-[#060606] text-right">
                            @if ($person->phones && count($person->phones) > 0)
                                @foreach ($person->phones as $phone)
                                    <div class="font-mono text-sm">+{{ $phone }}</div>
                                @endforeach
                            @else
                                <div class="text-[#7F7F7F]">-</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-start justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Почта</span>
                        <div class="text-[#060606] text-right">
                            @if ($person->emails && count($person->emails) > 0)
                                @foreach ($person->emails as $email)
                                    <div class="font-mono text-sm">{{ $email }}</div>
                                @endforeach
                            @else
                                <div class="text-[#7F7F7F]">-</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Фото</span>
                        <div class="text-right">
                            @if ($person->photo)
                                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-[#565A5B] bg-[#060606]">
                                    <img src="{{ asset('storage/' . $person->photo) }}" alt="Фото {{ $person->last_name }}"
                                        class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-full bg-[#BFBFBF] flex items-center justify-center">
                                    <svg class="w-8 h-8 text-[#565A5B]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Создан</span>
                        <span
                            class="text-[#060606]">{{ $person->created_at ? \Carbon\Carbon::parse($person->created_at)->format('d.m.Y H:i') : '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Обновлен</span>
                        <span
                            class="text-[#060606]">{{ $person->updated_at ? \Carbon\Carbon::parse($person->updated_at)->format('d.m.Y H:i') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
