@extends('layouts.main')

@section('header-title')
    {{ $commissariat->name }}
@endsection

@section('vite-resources')
    @vite(['resources/css/structure.css', 'resources/js/structure.js'])
@endsection

@section('content')
    {{-- Управляющие элементы --}}
    <div class="fixed top-5 right-5 z-50">
        <x-dropdown-menu 
            buttonText="Создание"
            :items="[
                ['route' => route('departments.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]), 'text' => 'Отдел'],
                ['route' => route('divisions.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]), 'text' => 'Отделение'],
                ['route' => route('employees.create', ['commissariat_id' => $commissariat->id, 'back_url' => url()->full()]), 'text' => 'Сотрудник']
            ]"
        />
    </div>

    <button id="resetView">Вернуться к центру</button>

    <a href="{{ route('structure.index') }}" class="absolute left-5 top-5 inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 z-10 bg-white/80 px-3 py-1 rounded-lg">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Назад
    </a>

    <div id="viewport">
        <div id="canvas">
            <div class="tree">
                {{-- Начальник комиссариата (слева) --}}
                <div class="boss-wrapper">
                    <x-structure-node 
                        :route="route('commissariats.show', ['id' => $commissariat->id, 'back_url' => url()->full()])"
                        :name="optional($commissariat->getChiefAttribute())?->getFullNameAttribute() ?? 'Не назначен начальник'" 
                        type="boss"
                    />
                </div>

                {{-- Правая часть со всей структурой --}}
                <div class="structure-wrapper">
                    {{-- Отделы --}}
                    @if($commissariat->departments->count())
                        <div class="departments-container">
                            <div class="departments">
                                @foreach($commissariat->departments as $department)
                                    <div class="department">
                                        <div class="dept-title font-bold text-black text-xl mb-3">{{ $department->name }}</div>
                                        
                                        <x-structure-node 
                                            :route="route('departments.show', ['id' => $department->id, 'back_url' => url()->full()])"
                                            :name="optional($department->getChiefAttribute())?->getFullNameAttribute() ?? 'Не назначен начальник отдела'"
                                        />

                                        {{-- Отделения отдела --}}
                                        @if($department->divisions->count())
                                            <div class="units">
                                                @foreach($department->divisions as $division)
                                                    <x-division-block 
                                                        :division="$division" 
                                                        :commissariat="$commissariat"
                                                        :backUrl="url()->full()"
                                                    />
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <x-add-button 
                                            :route="route('divisions.create', [
                                                'commissariat_id' => $commissariat->id,
                                                'department_id' => $department->id,
                                                'back_url' => url()->full(),
                                            ])"
                                            text="+ Добавить отделение"
                                            class="mt-3"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Сотрудники комиссариата --}}
                    @if($commissariat?->employeesNotIndependent()?->count())
                        <div class="employees-container">
                            <div class="employees">
                                @foreach($commissariat->employeesNotIndependent() as $employee)
                                    <x-employee-card 
                                        :employee="$employee"
                                        :backUrl="url()->full()"
                                    />
                                @endforeach
                                
                                <x-add-button 
                                    :route="route('employees.create', [
                                        'commissariat_id' => $commissariat->id,
                                        'back_url' => url()->full(),
                                    ])"
                                    text="+ Добавить сотрудника"
                                />
                            </div>
                        </div>
                    @endif

                    {{-- Самостоятельные сотрудники --}}
                    @if($commissariat?->employeesIndependent()?->count())
                        <div class="independent-employees-container">
                            <h3 class="text-lg font-semibold mb-3">Самостоятельные сотрудники</h3>
                            <div class="employees">
                                @foreach($commissariat->employeesIndependent() as $employee)
                                    <x-employee-card 
                                        :employee="$employee"
                                        :backUrl="url()->full()"
                                    />
                                @endforeach
                                
                                <x-add-button 
                                    :route="route('employees.create', [
                                        'commissariat_id' => $commissariat->id,
                                        'is_independent' => 1,
                                        'back_url' => url()->full(),
                                    ])"
                                    text="+ Добавить сотрудника"
                                />
                            </div>
                        </div>
                    @endif

                    {{-- Самостоятельные отделения --}}
                    @if($commissariat?->divisionsIntependent()?->count())
                        <div class="independent-divisions-container">
                            <h3 class="text-lg font-semibold mb-3">Самостоятельные отделения</h3>
                            <div class="units">
                                @foreach($commissariat->divisionsIntependent() as $division)
                                    <x-division-block 
                                        :division="$division" 
                                        :commissariat="$commissariat"
                                        :backUrl="url()->full()"
                                    />
                                @endforeach
                                
                                <x-add-button 
                                    :route="route('divisions.create', [
                                        'commissariat_id' => $commissariat->id,
                                        'back_url' => url()->full(),
                                    ])"
                                    text="+ Добавить отделение"
                                />
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection