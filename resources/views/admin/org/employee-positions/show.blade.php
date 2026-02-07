@extends('layouts.main')

@section('header-title')
    {{ $employee->person->last_name }}
    {{ $employee->person->first_name }}
    {{ $employee->person->patronymic }}
@endsection

@section('content')
    <div class="max-w-2xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('employee-positions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">
                {{ $employee->person->last_name }}
                {{ $employee->person->first_name }}
                {{ $employee->person->patronymic }}
            </h1>
            <p class="text-[#565A5B] mt-1">Детали должностей сотрудника</p>
        </div>

        <!-- Карточка с должностями -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                @forelse($employee->positions as $position)
                    <div class="bg-white/50 rounded-lg border border-[#BFBFBF] p-4 mb-4 last:mb-0">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#060606]">Должность</span>
                                <span class="text-[#060606]">{{ $position->position->name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#060606]">Тип должности</span>
                                <span class="text-[#060606]">{{ $position->position->positionType->name }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#060606]">Ставка</span>
                                <span class="text-[#060606]">{{ $position->rate }}</span>
                            </div>

                            @if ($position->commissariat_id)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Комиссариат</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->commissariat->name }}</span>
                                </div>
                            @endif

                            @if ($position->department_id)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Отдел</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->department->name }}</span>
                                </div>
                            @endif

                            @if ($position->division_id)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Отделение</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $position->division->name }}</span>
                                </div>
                            @endif

                            @if ($position->is_independent !== false)
                                <i
                                    style="color: rgb(17, 183, 17)">({{ $position->is_independent ? 'Самостоятельная должность' : '' }})</i>
                            @endif

                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-[#BFBFBF] mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <p class="text-[#565A5B] text-sm font-medium">Нет назначенных должностей</p>
                            <p class="text-[#7F7F7F] text-xs mt-1">У сотрудника пока нет должностей</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
