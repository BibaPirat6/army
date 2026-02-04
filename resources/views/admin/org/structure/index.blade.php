@extends('layouts.main')

@section('header-title')
    Комиссариаты
@endsection

@section(section: 'content')


    <div class="max-w-4xl p-6 mx-auto">
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <h1 class="text-2xl font-bold text-[#060606] mb-8">Комиссариаты</h1>

                @if ($commissariats?->count() > 0)
                    <ul class="space-y-3">
                        @foreach ($commissariats as $commissariat)
                            <li
                                class="bg-white rounded-lg border border-[#BFBFBF] p-4 hover:bg-[#A60644]/5 transition-colors duration-200">
                                <a href="{{ route('structure.show', $commissariat->id) }}"
                                    class="inline-flex items-center text-[#060606] font-medium hover:text-[#A60644] transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-3 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    {{ $commissariat->name }}
                                    <svg class="w-4 h-4 ml-2 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7"></path>
                                    </svg>

                                    <p>
                                        <b>Начальник комиссариата:</b>

                                        @if (!$commissariat->chiefEmployeePosition?->employee?->person)
                                            <span class="text-[#A60644]">Не назначен начальник</span>
                                        @else
                                            <span
                                                class="text-[#A60644]">{{ $commissariat->chiefEmployeePosition->employee->person->last_name }}
                                                {{ $commissariat->chiefEmployeePosition->employee->person->first_name }}
                                                {{ $commissariat->chiefEmployeePosition->employee->person->patronymic ?? '' }}</span>
                                        @endif
                                    </p>

                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            <p class="text-[#565A5B] text-lg font-medium">Нет доступных комиссариатов</p>
                            <p class="text-[#7F7F7F] mt-1">Создайте первый комиссариат для начала работы со структурой</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
