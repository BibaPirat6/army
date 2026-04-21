{{-- resources/views/admin/org/commissariat-positions/show.blade.php --}}
@extends('layouts.main')

@section('header-title')
    Детали штатной должности
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <div class="w-full p-6 mx-auto">
        <!-- Навигация -->
        <div class="flex items-center mb-6">
            <a href="{{ $backUrl ?? route('commissariat-positions.index', ['commissariat_id' => $commissariatPosition->commissariat_id]) }}"
                class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Назад
            </a>
        </div>

        <!-- Основная информация о должности -->
        <div class="bg-white rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden mb-8">
            <div class="bg-[#A60644] px-6 py-4">
                <h2 class="text-xl font-bold text-white">Информация о штатной должности</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-[#565A5B]">Должность</label>
                            <p class="text-lg font-bold text-[#060606]">
                                <a href="{{ route("positions.show", [
                                    "id"=>$commissariatPosition->position->id,
                                    "back_url"=>url()->full()
                                ]) }}">{{ $commissariatPosition->position->name }}</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-[#565A5B]">Тип должности</label>
                            <p class="text-[#060606]">
                                <a href="{{ route("position-types.show",[
                                    "id"=>$commissariatPosition->position->positionType->id,
                                    "back_url"=>url()->full()
                                ]) }}">{{ $commissariatPosition->position->positionType->name ?? 'Не указан' }}</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-[#565A5B]">Тип руководителя</label>
                            <p class="text-[#060606]">{{ $commissariatPosition->position->chiefType->name ?? 'Не указан' }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-[#565A5B]">Самостоятельная должность</label>
                            <p class="text-[#060606]">
                                @if($commissariatPosition->is_independent)
                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-green-100 text-green-800 text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Да
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-gray-800 text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Нет
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-[#565A5B]">Комиссариат</label>
                            <p class="text-[#060606]">
                                <a href="{{ route("commissariats.show",[
                                    "id"=>$commissariatPosition->commissariat->id,
                                    "back_url"=>url()->full()
                                ]) }}">{{ $commissariatPosition->commissariat->name }}</a>
                            </p>
                        </div>
                        
                        @if($commissariatPosition->department)
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-[#565A5B]">Отдел</label>
                            <p class="text-[#060606]">
                                <a href="{{ route("departments.show",[
                                    "id"=>$commissariatPosition->department->id,
                                    "back_url"=>url()->full()
                                ]) }}">{{ $commissariatPosition->department->name }}</a>
                            </p>
                        </div>
                        @endif
                        
                        @if($commissariatPosition->division)
                        <div class="mb-4">
                            <label class="text-sm font-semibold text-[#565A5B]">Отделение</label>
                            <p class="text-[#060606]">
                                  <a href="{{ route("divisions.show",[
                                    "id"=>$commissariatPosition->division->id,
                                    "back_url"=>url()->full()
                                ]) }}">{{ $commissariatPosition->division->name }}</a>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Информация о ставках -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-[#BFBFBF] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[#565A5B] mb-1">Всего ставок</p>
                        <p class="text-3xl font-bold text-[#A60644]">{{ number_format($statistics['total_rate'], 2) }}</p>
                    </div>
                    <div class="bg-[#A60644]/10 p-3 rounded-full">
                        <svg class="w-8 h-8 text-[#A60644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg border border-[#BFBFBF] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[#565A5B] mb-1">Занято ставок</p>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($statistics['occupied_rate'], 2) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg border border-[#BFBFBF] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[#565A5B] mb-1">Доступно ставок</p>
                        <p class="text-3xl font-bold {{ $hasVacancy ? 'text-blue-600' : 'text-red-600' }}">
                            {{ number_format($statistics['available_rate'], 2) }}
                        </p>
                    </div>
                    <div class="{{ $hasVacancy ? 'bg-blue-100' : 'bg-red-100' }} p-3 rounded-full">
                        <svg class="w-8 h-8 {{ $hasVacancy ? 'text-blue-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Кнопка назначения (показываем только если есть вакансия) -->
        @if($hasVacancy)
        <div class="mb-6">
            <a href="{{ route('commissariat-positions.assign.form', [
                'id' => $commissariatPosition->id,
                'back_url' => url()->full(),
                'commissariat_id' => $commissariatPosition->commissariat_id
            ]) }}"
                class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Назначить сотрудника (доступно {{ number_format($availableRate, 2) }} ставок)
            </a>
        </div>
        @else
        <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-yellow-700">Все ставки заняты. Чтобы назначить нового сотрудника, освободите ставку (измените статус существующего сотрудника или уменьшите его ставку).</p>
            </div>
        </div>
        @endif

        <!-- Список назначений -->
        <div class="bg-white rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="bg-[#565A5B] px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Назначения сотрудников</h2>
                <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm">
                    Всего: {{ $statistics['total_assignments'] }} | 
                    Активных: {{ $statistics['active_assignments'] }}
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#e7e1e1]">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#060606]">Сотрудник</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#060606]">Ставка</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#060606]">Статус</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#060606]">Занимает ставку</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#060606]">Дата назначения</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#060606]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($commissariatPosition->employeePositions as $assignment)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-6 py-4">
                                    <a href="{{ route('employees.show', [
                                        'id' => $assignment->employee->id,
                                        'back_url' => url()->full()
                                    ]) }}" class="text-[#A60644] hover:underline font-medium">
                                        {{ $assignment->employee->getFullNameAttribute() }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    {{ number_format($assignment->rate, 2) }}
                                </td>
                                
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-sm font-medium" 
                                          style="background-color: #000020; color: #{{ $assignment->employeePositionStatus->color }}">
                                        {{ $assignment->employeePositionStatus->name }}
                                    </span>
                                </td>
                                {{-- <td class="px-6 py-4">
                                    @if($assignment->status->occupies_rate)
                                        <span class="inline-flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Да
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-red-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Нет
                                        </span>
                                    @endif
                                </td> --}}
                                <td class="px-6 py-4 text-sm text-[#565A5B]">
                                    {{ $assignment->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('employee-positions.edit', $assignment->id) }}"
                                            class="inline-flex items-center px-3 py-1 bg-[#A60644] text-white text-sm rounded-lg hover:bg-[#A60644]/80">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Изменить
                                        </a>
                                        
                                        {{-- <form action="{{ route('commissariat-positions.assignments.destroy', $assignment->id) }}" 
                                              method="POST" class="inline-block"
                                              onsubmit="return confirm('Вы уверены, что хотите удалить это назначение?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Удалить
                                            </button>
                                        </form> --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <p class="text-[#565A5B] text-lg font-medium">Нет назначений на эту должность</p>
                                        @if($hasVacancy)
                                            <p class="text-sm text-[#565A5B] mt-2">Нажмите кнопку "Назначить сотрудника" чтобы добавить</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Прогресс-бар занятости ставок -->
        <div class="mt-8 bg-white rounded-2xl shadow-lg border border-[#BFBFBF] p-6">
            <h3 class="text-lg font-bold text-[#060606] mb-4">Занятость ставок</h3>
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block text-[#A60644]">
                            {{ number_format($statistics['occupied_rate'], 2) }} / {{ number_format($statistics['total_rate'], 2) }} ставок занято
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-[#A60644]">
                            {{ $statistics['vacancy_percent'] }}% свободно
                        </span>
                    </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                    <div style="width: {{ ($statistics['occupied_rate'] / $statistics['total_rate']) * 100 }}%"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-[#A60644]">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection