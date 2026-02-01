@extends('layouts.main')

@section('header-title')
    {{ $commissariat['name'] }}
@endsection

@section('content')
    <div class="max-w-2xl p-6 mx-auto">
        <div class="flex items-center mb-4">
            <a href="{{ route('commissariats.index') }}"
                class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Назад
            </a>
        </div>

        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-[#060606]">{{ $commissariat['name'] }}</h1>
                    <p class="text-[#565A5B] mt-1">Детали комиссариата</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">ID</span>
                        <span class="text-[#060606]">{{ $commissariat['id'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Начальник</span>



                        <span class="text-[#060606]">
                            @if ($commissariat->chiefEmployeePosition !== null)
                                @if ($commissariat->chiefEmployeePosition->employee && $commissariat->chiefEmployeePosition->employee->person)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $commissariat->chiefEmployeePosition->employee->person->last_name ?? '*' }}
                                        {{ $commissariat->chiefEmployeePosition->employee->person->first_name ?? '*' }}
                                        {{ $commissariat->chiefEmployeePosition->employee->person->patronymic ?? '*' }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Без ФИО (ID:
                                        {{ $commissariat->chiefEmployeePosition->id }})</span>
                                @endif
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Нет
                                </span>
                            @endif
                        </span>
                    </div>


                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Создан</span>
                        <span
                            class="text-[#060606]">{{ \Carbon\Carbon::parse($commissariat['created_at'])->format('d.m.Y H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Обновлен</span>
                        <span
                            class="text-[#060606]">{{ \Carbon\Carbon::parse($commissariat['updated_at'])->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
