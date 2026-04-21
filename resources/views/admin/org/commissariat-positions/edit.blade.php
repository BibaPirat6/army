@extends('layouts.main')

@section('header-title')
    Редактирование штатной должности
@endsection

@section('content')
    <div class="w-full p-6 mx-auto">
        <!-- Заголовок и кнопка назад -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center mb-4">
                    <a href="{{ $backUrl ?? route('commissariat-positions.index') }}"
                        class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Назад
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-[#060606]">Редактирование штатной должности</h1>
            </div>
        </div>

        <!-- Форма редактирования -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden p-6">
            <form action="{{ route('commissariat-positions.update', $commissariatPosition->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="back_url" value="{{ $backUrl }}">
                <input type="hidden" name="commissariat_id" value="{{ $commissariat->id }}">

                <div class="grid gap-6 mb-6 md:grid-cols-2">
                    <!-- Отдел -->
                    <div>
                        <label for="department_id" class="block mb-2 text-sm font-medium text-[#060606]">Отдел</label>
                        <select id="department_id" name="department_id"
                            class="bg-[#e7e1e1] border border-[#BFBFBF] text-[#060606] text-sm rounded-lg focus:ring-[#A60644] focus:border-[#A60644] block w-full p-2.5">
                            <option value="">Не выбрано</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                    {{ optional($commissariatPosition->department)->id == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Отделение -->
                    <div>
                        <label for="division_id" class="block mb-2 text-sm font-medium text-[#060606]">Отделение</label>
                        <select id="division_id" name="division_id"
                            class="bg-[#e7e1e1] border border-[#BFBFBF] text-[#060606] text-sm rounded-lg focus:ring-[#A60644] focus:border-[#A60644] block w-full p-2.5">
                            <option value="">Не выбрано</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ optional($commissariatPosition->division)->id == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Должность -->
                    <div>
                        <label for="position_id" class="block mb-2 text-sm font-medium text-[#060606]">Должность</label>
                        <select id="position_id" name="position_id" required
                            class="bg-[#e7e1e1] border border-[#BFBFBF] text-[#060606] text-sm rounded-lg focus:ring-[#A60644] focus:border-[#A60644] block w-full p-2.5">
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}"
                                    {{ $commissariatPosition->position_id == $position->id ? 'selected' : '' }}>
                                    {{ $position->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Общая ставка -->
                    <div>
                        <label for="rate_total" class="block mb-2 text-sm font-medium text-[#060606]">Общая ставка</label>
                        <input type="number" id="rate_total" name="rate_total" step="0.25" min="0.25" max="2.00" 
                            value="{{ $commissariatPosition->rate_total }}" required
                            class="bg-[#e7e1e1] border border-[#BFBFBF] text-[#060606] text-sm rounded-lg focus:ring-[#A60644] focus:border-[#A60644] block w-full p-2.5">
                    </div>

                    <!-- Независимая должность -->
                    <div class="flex items-center">
                        <input id="is_independent" name="is_independent" type="checkbox" 
                            {{ $commissariatPosition->is_independent ? 'checked' : '' }}
                            class="w-4 h-4 text-[#A60644] bg-[#e7e1e1] border-[#BFBFBF] rounded focus:ring-[#A60644]">
                        <label for="is_independent" class="ml-2 text-sm font-medium text-[#060606]">Независимая должность</label>
                    </div>
                </div>

                <!-- Список назначений -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-[#060606] mb-4">Назначения сотрудников</h3>
                    @foreach($commissariatPosition->employeePositions as $assignment)
                        <div class="grid gap-6 mb-4 md:grid-cols-3 bg-[#f5f5f5] p-4 rounded-lg">
                            <!-- Сотрудник -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-[#060606]">Сотрудник</label>
                                <select name="assignments[{{ $assignment->id }}][employee_id]"
                                    class="bg-[#e7e1e1] border border-[#BFBFBF] text-[#060606] text-sm rounded-lg focus:ring-[#A60644] focus:border-[#A60644] block w-full p-2.5">
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ $assignment->employee_id == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->getFullNameAttribute() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Ставка -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-[#060606]">Ставка</label>
                                <input type="number" name="assignments[{{ $assignment->id }}][rate]" 
                                    step="0.25" min="0.25" max="2.00" value="{{ $assignment->rate }}"
                                    class="bg-[#e7e1e1] border border-[#BFBFBF] text-[#060606] text-sm rounded-lg focus:ring-[#A60644] focus:border-[#A60644] block w-full p-2.5">
                            </div>

                            <!-- Статус -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-[#060606]">Статус</label>
                                <select name="assignments[{{ $assignment->id }}][employee_position_status_id]"
                                    class="bg-[#e7e1e1] border border-[#BFBFBF] text-[#060606] text-sm rounded-lg focus:ring-[#A60644] focus:border-[#A60644] block w-full p-2.5">
                                    @foreach($employeePositionStatuses as $status)
                                        <option value="{{ $status->id }}"
                                            {{ $assignment->employee_position_status_id == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Кнопка сохранения -->
                <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Сохранить изменения
                </button>
            </form>
        </div>
    </div>
@endsection