@extends('layouts.main')

@section('header-title')
    {{ $commissariat->name }}
@endsection

@section('vite-resources')
    @vite(['resources/css/structure.css', 'resources/js/structure.js'])
@endsection

@section('content')
    <!-- Контейнер группы кнопок -->
    <div class="fixed bottom-5 right-5 z-[1000] flex flex-col gap-3">

        <!-- Зеленая кнопка - Штат -->
        <a href="{{ route("commissariat-positions.index", [
        "commissariat_id" => $commissariat->id,
        "back_url" => url()->full()
    ]) }}"
            class="px-4 py-2.5 rounded-xl bg-[#1ba606] text-white text-sm font-medium hover:bg-green-600 transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95 text-center">
            Штатные должности
        </a>
        
        <!-- Розовая кнопка - Узловая структура -->
        <a href="{{ route("structure.obsidian", [
        "id" => $commissariat->id,
        "back_url" => url()->full()
    ]) }}"
            class="px-4 py-2.5 rounded-xl bg-[#A60644] text-white text-sm font-medium hover:bg-pink-600 transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95 text-center">
            Узловая структура
        </a>

        <!-- Тёмная кнопка - Вернуться к центру -->
        <button id="resetView"
            class="px-4 py-2.5 rounded-xl bg-[#060606] text-white text-sm font-medium hover:bg-[#060606]/80 transition-all duration-200 shadow-lg hover:shadow-xl active:scale-95">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
            Вернуться к центру
        </button>

    </div>

    {{-- Кнопка назад --}}
    <a href="{{ route('structure.index') }}"
        class="fixed left-5 top-20 z-[100] inline-flex items-center gap-2 px-4 py-2 bg-white/90 backdrop-blur-sm rounded-xl text-[#A60644] font-medium hover:bg-white shadow-md hover:shadow-lg transition-all duration-200 group">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Назад
    </a>

    {{-- Основной контейнер для панорамирования --}}
    <div id="viewport"
        class="w-screen h-screen bg-gradient-to-br from-[#f7f3f3] to-[#e8e4e4] cursor-grab active:cursor-grabbing overflow-hidden">
        <div id="canvas" class="absolute left-0 top-0 inline-block min-w-max p-8">
            <div class="tree flex flex-col items-center pt-[50px]">
                <div class="boss-wrapper flex flex-col items-center relative pt-[50px]">

                    {{-- Начальник комиссариата --}}
                    <a href="{{ route('commissariats.show', ['id' => $commissariat->id, 'back_url' => url()->full()]) }}"
                        class="chief-link block w-[360px] bg-gradient-to-r from-[#060606] to-[#1a1a1a] rounded-2xl shadow-2xl border border-white/10 card-hover no-underline">
                        <div class="flex items-center gap-4 p-5">
                            <div class="flex-1">
                                <div class="text-xs text-white/60 uppercase tracking-wider mb-1">Начальник комиссариата
                                </div>
                                @if(optional($commissariat->getChiefAttribute()))
                                    <div class="text-white font-semibold text-lg leading-tight">
                                        {{ optional($commissariat->getChiefAttribute())->getFullNameAttribute() ?? "" }}
                                    </div>
                                @else
                                    <div class="text-white/50 italic">Не назначен</div>
                                @endif
                            </div>
                            <div class="relative group">
                                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center 
                                    hover:bg-[#A60644] hover:scale-110 hover:shadow-md transition-all duration-200 cursor-pointer">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M12 18h.01">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>

                    {{-- Линии --}}
                    <div class="lines-to-departments">
                        <div class="line vertical"></div>
                        <div class="line horizontal"></div>
                    </div>

                    {{-- ОТДЕЛЫ И ОСТАЛЬНЫЕ КАРТОЧКИ В ОДНУ ЛИНИЮ --}}
                    <div class="departments-container">

                        {{-- Карточки отделов --}}
                        @foreach ($commissariat->departments as $department)
                            @php
                                // Получаем ВСЕ штатные должности отдела (без отделений)
                                $departmentPositions = $department->commissariatPositions()
                                    ->whereNull('division_id')
                                    ->with(['position', 'employeePositions' => function($q) {
                                        $q->with(['employee.person', 'employeePositionStatus']);
                                    }])
                                    ->get();
                                
                                // Получаем начальника отдела
                                $departmentChief = $department->getChiefAttribute();
                                $departmentChiefId = optional($departmentChief)->id;
                            @endphp

                            <div
                                class="department-card w-[340px] bg-white rounded-2xl shadow-xl border border-[#BFBFBF]/30 overflow-hidden card-hover flex flex-col">
                                {{-- Заголовок отдела --}}
                                <div class="bg-gradient-to-r from-[#A60644] to-[#6b0229] px-5 py-3 flex-shrink-0">
                                    <h3 class="text-white font-bold text-xl uppercase tracking-wide">{{ $department->name }}
                                    </h3>
                                    <p class="text-white/60 text-xs mt-1">Отдел</p>
                                </div>

                                {{-- Начальник отдела --}}
                                <div class="p-4 border-b border-[#BFBFBF]/20 flex-shrink-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="text-xs text-[#565A5B] uppercase tracking-wider">Начальник отдела</div>
                                            @if($departmentChief)
                                                <div class="text-[#060606] font-semibold">
                                                    {{ $departmentChief->getFullNameAttribute() ?? "" }}
                                                </div>
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    {{-- {{ $departmentChief->getCurrentPositionName() ?? '' }} --}}
                                                </div>
                                            @else
                                                <div class="text-[#565A5B] italic text-sm">Не назначен</div>
                                            @endif
                                        </div>
                                        <a href="{{ route('departments.show', ['id' => $department->id, 'back_url' => url()->full()]) }}"
                                            class="info-btn w-8 h-8 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#A60644]" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M12 16v.01" stroke-linecap="round"></path>
                                                <path d="M12 13a2 2 0 0 0 .914-3.782 1.98 1.98 0 0 0-2.414.483"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                {{-- Штатные должности отдела --}}
                                <div class="p-4 space-y-3 flex-1 overflow-y-auto custom-scroll smooth-content"
                                    style="max-height: 400px;">
                                    
                                    @forelse($departmentPositions as $position)
                                        @php
                                            // Считаем ТОЛЬКО активные назначения (которые занимают ставку)
                                            $activeAssignments = $position->employeePositions->filter(function($ep) {
                                                return $ep->employeePositionStatus && $ep->employeePositionStatus->occupies_rate;
                                            });
                                            
                                            $occupiedRate = $activeAssignments->sum('rate');
                                            $availableRate = $position->rate_total - $occupiedRate;
                                            $hasVacancy = $availableRate > 0;
                                        @endphp

                                        <div class="bg-[#f5f5f5] rounded-xl p-3 border {{ $hasVacancy ? 'border-green-400 bg-green-50' : 'border-[#BFBFBF]/20' }} transition-all duration-200">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex-1">
                                                    <div class="font-bold text-[#060606] text-sm">
                                                        {{ $position->position->name }}
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs text-gray-500">
                                                            ставок: {{ number_format($position->rate_total, 2) }}
                                                        </span>
                                                        <span class="text-xs {{ $hasVacancy ? 'text-green-600' : 'text-orange-600' }}">
                                                            (занято: {{ number_format($occupiedRate, 2) }} | свободно: {{ number_format($availableRate, 2) }})
                                                        </span>
                                                    </div>
                                                </div>
                                                @if($hasVacancy)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                        Вакансия
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Назначения на эту должность --}}
                                            @if($activeAssignments->count() > 0)
                                                <div class="mt-2 space-y-1.5">
                                                    @foreach($activeAssignments as $assignment)
                                                        <a href="{{ route('employees.show', ['id' => $assignment->employee->id, 'back_url' => url()->full()]) }}"
                                                            class="group flex items-center justify-between w-full bg-white rounded-lg p-2 border border-[#BFBFBF]/20 hover:border-[#A60644]/50 hover:bg-[#A60644]/5 transition-all duration-200 cursor-pointer">
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-sm text-[#060606] font-medium truncate">
                                                                    {{ $assignment->employee->getFullNameAttribute() ?? 'Нет данных' }}
                                                                </div>
                                                            </div>
                                                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium ml-2 flex-shrink-0">
                                                                ставка: {{ number_format($assignment->rate, 2) }}
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center text-[#565A5B] py-2 text-xs italic mt-2">Нет назначений</div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-center text-[#565A5B] py-4 italic">Нет штатных должностей</div>
                                    @endforelse

                                    {{-- Отделения отдела --}}
                                    @if($department->divisions->count() > 0)
                                        @foreach ($department->divisions as $division)
                                            @php
                                                $divisionPositions = $division->commissariatPositions()
                                                    ->with(['position', 'employeePositions' => function($q) {
                                                        $q->with(['employee.person', 'employeePositionStatus']);
                                                    }])
                                                    ->get();
                                                
                                                $divisionChief = $division->getChiefAttribute();
                                                $divisionChiefId = optional($divisionChief)->id;
                                            @endphp

                                            <div class="bg-[#f5f5f5] rounded-xl p-3 border border-[#BFBFBF]/20 hover:border-[#A60644]/30 transition-all duration-200">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="font-bold text-[#060606] text-sm uppercase tracking-wide">
                                                        {{ $division->name }}
                                                    </h4>
                                                    <a href="{{ route('divisions.show', ['id' => $division->id, 'back_url' => url()->full()]) }}"
                                                        class="info-btn w-6 h-6 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-[#A60644]" fill="none" stroke="currentColor"
                                                            stroke-width="2" viewBox="0 0 24 24">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <path d="M12 16v.01" stroke-linecap="round"></path>
                                                            <path d="M12 13a2 2 0 0 0 .914-3.782 1.98 1.98 0 0 0-2.414.483"></path>
                                                        </svg>
                                                    </a>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="text-xs text-[#565A5B]">Начальник отделения</div>
                                                    @if($divisionChief)
                                                        <div class="text-[#060606] font-medium text-sm">
                                                            {{ $divisionChief->getFullNameAttribute() ?? "" }}
                                                        </div>
                                                        <div class="text-xs text-gray-400">
                                                            {{-- {{ $divisionChief->getCurrentPositionName() ?? '' }} --}}
                                                        </div>
                                                    @else
                                                        <div class="text-[#565A5B] italic text-xs">Не назначен</div>
                                                    @endif
                                                </div>

                                                {{-- Штатные должности отделения --}}
                                                @forelse($divisionPositions as $position)
                                                    @php
                                                        $activeAssignments = $position->employeePositions->filter(function($ep) {
                                                            return $ep->employeePositionStatus && $ep->employeePositionStatus->occupies_rate;
                                                        });
                                                        
                                                        $occupiedRate = $activeAssignments->sum('rate');
                                                        $availableRate = $position->rate_total - $occupiedRate;
                                                        $hasVacancy = $availableRate > 0;
                                                    @endphp

                                                    <div class="mt-3 bg-white rounded-lg p-2 border {{ $hasVacancy ? 'border-green-400 bg-green-50' : 'border-[#BFBFBF]/20' }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="font-medium text-[#060606] text-xs">
                                                                    {{ $position->position->name }}
                                                                </div>
                                                                <div class="flex items-center gap-2 mt-0.5">
                                                                    <span class="text-xs text-gray-500">
                                                                        {{ number_format($position->rate_total, 2) }} став.
                                                                    </span>
                                                                    @if($hasVacancy)
                                                                        <span class="text-xs text-green-600">свободно {{ number_format($availableRate, 2) }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if($hasVacancy)
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    Вакант
                                                                </span>
                                                            @endif
                                                        </div>

                                                        @if($activeAssignments->count() > 0)
                                                            <div class="mt-2 space-y-1">
                                                                @foreach($activeAssignments as $assignment)
                                                                    <a href="{{ route('employees.show', ['id' => $assignment->employee->id, 'back_url' => url()->full()]) }}"
                                                                        class="group flex items-center justify-between w-full bg-white rounded p-1.5 hover:bg-[#A60644]/5 transition-all duration-200 cursor-pointer">
                                                                        <span class="text-xs text-[#060606] truncate">
                                                                            {{ $assignment->employee->getFullNameAttribute() ?? 'Нет данных' }}
                                                                        </span>
                                                                        <span class="text-xs text-gray-500">
                                                                            ставка: {{ number_format($assignment->rate, 2) }}
                                                                        </span>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="text-center text-[#565A5B] py-2 text-xs italic">Нет штатных должностей</div>
                                                @endforelse
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- КАРТОЧКА ШТАТНЫХ ДОЛЖНОСТЕЙ КОМИССАРИАТА --}}
                        @php
                            // Получаем ВСЕ штатные должности комиссариата (без отдела и отделения)
                            $commissariatPositions = $commissariat->commissariatPositions()
                                ->whereNull('department_id')
                                ->whereNull('division_id')
                                ->where('is_independent', false)
                                ->with(['position', 'employeePositions' => function($q) {
                                    $q->with(['employee.person', 'employeePositionStatus']);
                                }])
                                ->get();
                            
                            // Получаем самостоятельные должности
                            $independentPositions = $commissariat->commissariatPositions()
                                ->where('is_independent', true)
                                ->with(['position', 'employeePositions' => function($q) {
                                    $q->with(['employee.person', 'employeePositionStatus']);
                                }])
                                ->get();
                            
                            $hasAnyPositions = $commissariatPositions->count() > 0 || $independentPositions->count() > 0;
                        @endphp

                        @if($hasAnyPositions)
                            <div class="department-card w-[340px] bg-white rounded-2xl shadow-xl border border-[#BFBFBF]/30 overflow-hidden card-hover">
                                <div class="bg-gradient-to-r from-[#060606] to-[#1a1a1a] px-5 py-3">
                                    <h3 class="text-white font-bold text-lg">Штатные должности</h3>
                                    <p class="text-white/60 text-xs mt-1">Комиссариат</p>
                                </div>
                                <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto">

                                    {{-- Обычные должности --}}
                                    @foreach($commissariatPositions as $position)
                                        @php
                                            $activeAssignments = $position->employeePositions->filter(function($ep) {
                                                return $ep->employeePositionStatus && $ep->employeePositionStatus->occupies_rate;
                                            });
                                            
                                            $occupiedRate = $activeAssignments->sum('rate');
                                            $availableRate = $position->rate_total - $occupiedRate;
                                            $hasVacancy = $availableRate > 0;
                                        @endphp

                                        <div class="bg-[#f5f5f5] rounded-xl p-3 border {{ $hasVacancy ? 'border-green-400 bg-green-50' : 'border-[#BFBFBF]/20' }} transition-all duration-200">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex-1">
                                                    <div class="font-bold text-[#060606] text-sm">
                                                        {{ $position->position->name }}
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs text-gray-500">
                                                            ставок: {{ number_format($position->rate_total, 2) }}
                                                        </span>
                                                        <span class="text-xs {{ $hasVacancy ? 'text-green-600' : 'text-orange-600' }}">
                                                            (занято: {{ number_format($occupiedRate, 2) }} | свободно: {{ number_format($availableRate, 2) }})
                                                        </span>
                                                    </div>
                                                </div>
                                                @if($hasVacancy)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                        Вакансия
                                                    </span>
                                                @endif
                                            </div>

                                            @if($activeAssignments->count() > 0)
                                                <div class="mt-2 space-y-1.5">
                                                    @foreach($activeAssignments as $assignment)
                                                        <a href="{{ route('employees.show', ['id' => $assignment->employee->id, 'back_url' => url()->full()]) }}"
                                                            class="group flex items-center justify-between w-full bg-white rounded-lg p-2 border border-[#BFBFBF]/20 hover:border-[#A60644]/50 hover:bg-[#A60644]/5 transition-all duration-200 cursor-pointer">
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-sm text-[#060606] font-medium truncate">
                                                                    {{ $assignment->employee->getFullNameAttribute() ?? 'Нет данных' }}
                                                                </div>
                                                            </div>
                                                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium ml-2 flex-shrink-0">
                                                                ставка: {{ number_format($assignment->rate, 2) }}
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center text-[#565A5B] py-2 text-xs italic mt-2">Нет назначений</div>
                                            @endif
                                        </div>
                                    @endforeach

                                    {{-- Самостоятельные должности --}}
                                    @if($independentPositions->count() > 0)
                                        <div class="mt-3 pt-2 border-t border-[#BFBFBF]/30">
                                            <div class="text-xs font-semibold text-[#565A5B] uppercase tracking-wider mb-2 flex items-center gap-2">
                                                <div class="w-1 h-4 bg-[#A60644] rounded-full"></div>
                                                Самостоятельные должности
                                            </div>
                                            
                                            @foreach($independentPositions as $position)
                                                @php
                                                    $activeAssignments = $position->employeePositions->filter(function($ep) {
                                                        return $ep->employeePositionStatus && $ep->employeePositionStatus->occupies_rate;
                                                    });
                                                    
                                                    $occupiedRate = $activeAssignments->sum('rate');
                                                    $availableRate = $position->rate_total - $occupiedRate;
                                                    $hasVacancy = $availableRate > 0;
                                                @endphp

                                                <div class="bg-[#f5f5f5] rounded-xl p-3 border {{ $hasVacancy ? 'border-green-400 bg-green-50' : 'border-[#BFBFBF]/20' }} transition-all duration-200 mb-2">
                                                    <div class="flex items-start justify-between mb-2">
                                                        <div class="flex-1">
                                                            <div class="font-bold text-[#060606] text-sm">
                                                                {{ $position->position->name }}
                                                            </div>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="text-xs text-gray-500">
                                                                    ставок: {{ number_format($position->rate_total, 2) }}
                                                                </span>
                                                                <span class="text-xs {{ $hasVacancy ? 'text-green-600' : 'text-orange-600' }}">
                                                                    (занято: {{ number_format($occupiedRate, 2) }} | свободно: {{ number_format($availableRate, 2) }})
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @if($hasVacancy)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                Вакансия
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if($activeAssignments->count() > 0)
                                                        <div class="mt-2 space-y-1.5">
                                                            @foreach($activeAssignments as $assignment)
                                                                <a href="{{ route('employees.show', ['id' => $assignment->employee->id, 'back_url' => url()->full()]) }}"
                                                                    class="group flex items-center justify-between w-full bg-white rounded-lg p-2 border border-[#BFBFBF]/20 hover:border-[#A60644]/50 hover:bg-[#A60644]/5 transition-all duration-200 cursor-pointer">
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="text-sm text-[#060606] font-medium truncate">
                                                                            {{ $assignment->employee->getFullNameAttribute() ?? 'Нет данных' }}
                                                                        </div>
                                                                    </div>
                                                                    <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium ml-2 flex-shrink-0">
                                                                        ставка: {{ number_format($assignment->rate, 2) }}
                                                                    </span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="text-center text-[#565A5B] py-2 text-xs italic mt-2">Нет назначений</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- САМОСТОЯТЕЛЬНЫЕ ОТДЕЛЕНИЯ (вне отделов) --}}
                        @php
                            $divisionsIndependent = $commissariat->divisions()->whereNull('department_id')->get();
                        @endphp

                        @if($divisionsIndependent->count() > 0)
                            @foreach ($divisionsIndependent as $division)
                                @php
                                    $divisionPositions = $division->commissariatPositions()
                                        ->with(['position', 'employeePositions' => function($q) {
                                            $q->with(['employee.person', 'employeePositionStatus']);
                                        }])
                                        ->get();
                                    
                                    $divisionChief = $division->getChiefAttribute();
                                @endphp

                                <div class="department-card w-[340px] bg-white rounded-2xl shadow-xl border border-[#BFBFBF]/30 overflow-hidden card-hover flex flex-col">
                                    <div class="bg-gradient-to-r from-[#7F7F7F] to-[#5a5a5a] px-5 py-3 flex-shrink-0">
                                        <h3 class="text-white font-bold text-lg uppercase tracking-wide">{{ $division->name }}</h3>
                                        <p class="text-white/70 text-xs mt-1">Самостоятельное отделение</p>
                                    </div>

                                    {{-- Начальник отделения --}}
                                    <div class="p-4 border-b border-[#BFBFBF]/20 flex-shrink-0">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="text-xs text-[#565A5B] uppercase tracking-wider">Начальник</div>
                                                @if($divisionChief)
                                                    <div class="text-[#060606] font-semibold">
                                                        {{ $divisionChief->getFullNameAttribute() ?? "" }}
                                                    </div>
                                                    <div class="text-xs text-gray-400">
                                                        {{-- {{ $divisionChief->getCurrentPositionName() ?? '' }} --}}
                                                    </div>
                                                @else
                                                    <div class="text-[#565A5B] italic text-sm">Не назначен</div>
                                                @endif
                                            </div>
                                            <a href="{{ route('divisions.show', ['id' => $division->id, 'back_url' => url()->full()]) }}"
                                                class="info-btn w-8 h-8 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-[#A60644]" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M12 16v.01" stroke-linecap="round"></path>
                                                    <path d="M12 13a2 2 0 0 0 .914-3.782 1.98 1.98 0 0 0-2.414.483"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Штатные должности отделения --}}
                                    <div class="p-4 space-y-3 flex-1 overflow-y-auto custom-scroll smooth-content"
                                        style="max-height: 400px;">

                                        @forelse($divisionPositions as $position)
                                            @php
                                                $activeAssignments = $position->employeePositions->filter(function($ep) {
                                                    return $ep->employeePositionStatus && $ep->employeePositionStatus->occupies_rate;
                                                });
                                                
                                                $occupiedRate = $activeAssignments->sum('rate');
                                                $availableRate = $position->rate_total - $occupiedRate;
                                                $hasVacancy = $availableRate > 0;
                                            @endphp

                                            <div class="bg-[#f5f5f5] rounded-xl p-3 border {{ $hasVacancy ? 'border-green-400 bg-green-50' : 'border-[#BFBFBF]/20' }} transition-all duration-200">
                                                <div class="flex items-start justify-between mb-2">
                                                    <div class="flex-1">
                                                        <div class="font-bold text-[#060606] text-sm">
                                                            {{ $position->position->name }}
                                                        </div>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="text-xs text-gray-500">
                                                                ставок: {{ number_format($position->rate_total, 2) }}
                                                            </span>
                                                            <span class="text-xs {{ $hasVacancy ? 'text-green-600' : 'text-orange-600' }}">
                                                                (занято: {{ number_format($occupiedRate, 2) }} | свободно: {{ number_format($availableRate, 2) }})
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if($hasVacancy)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            Вакансия
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($activeAssignments->count() > 0)
                                                    <div class="mt-2 space-y-1.5">
                                                        @foreach($activeAssignments as $assignment)
                                                            <a href="{{ route('employees.show', ['id' => $assignment->employee->id, 'back_url' => url()->full()]) }}"
                                                                class="group flex items-center justify-between w-full bg-white rounded-lg p-2 border border-[#BFBFBF]/20 hover:border-[#A60644]/50 hover:bg-[#A60644]/5 transition-all duration-200 cursor-pointer">
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="text-sm text-[#060606] font-medium truncate">
                                                                        {{ $assignment->employee->getFullNameAttribute() ?? 'Нет данных' }}
                                                                    </div>
                                                                </div>
                                                                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium ml-2 flex-shrink-0">
                                                                    ставка: {{ number_format($assignment->rate, 2) }}
                                                                </span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-center text-[#565A5B] py-2 text-xs italic mt-2">Нет назначений</div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center text-[#565A5B] py-4 italic">Нет штатных должностей</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection