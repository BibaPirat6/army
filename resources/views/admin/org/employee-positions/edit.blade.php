@extends('layouts.main')

@section('header-title')
    Обновление назначения должности сотруднику
@endsection

@section('content')
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="max-w-4xl mx-auto p-6">
        <!-- Заголовок и ссылка назад -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('employee-positions.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад к списку назначений
                </a>
            </div>
            <h1 class="text-2xl font-bold text-[#060606]">Обновление назначения должности сотруднику</h1>
            <p class="text-[#565A5B] mt-1">Редактирование назначений для: "{{ $employee->person->last_name ?? '' }}
                {{ $employee->person->first_name ?? '' }}"</p>
        </div>

        <!-- Назначения должностей -->
        @foreach ($employee->positions as $position)
            <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden mb-6">
                <div class="p-6 md:p-8">
                    <h4 class="font-semibold text-[#565A5B] mb-4">Должность</h4>

                    <!-- Информация о текущей должности -->
                    <div class="bg-white/50 rounded-lg p-4 mb-4 border border-[#BFBFBF]">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#565A5B]">Название</span>
                                <span class="text-[#060606]">{{ $position->position->name ?? '' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#565A5B]">Ставка</span>
                                <span class="text-[#060606]">{{ $position->rate ?? '' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-[#565A5B]">Комиссариат</span>
                                <span class="text-[#060606]">{{ $position->commissariat->name ?? '' }}</span>
                            </div>

                            @if ($position->department)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Отдел</span>
                                    <span class="text-[#060606]">{{ $position->department->name ?? '' }}</span>
                                </div>
                            @endif
                            @if ($position->division)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Отделение</span>
                                    <span class="text-[#060606]">{{ $position->division->name ?? '' }}</span>
                                </div>
                            @endif

                            @if ($position->is_independent !== false)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-[#565A5B]">Самостоятельная</span>
                                    <span class="text-[#060606]">{{ $position->is_independent ? 'Да' : 'Нет' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Форма обновления -->
                    <form action="{{ route('employee-positions.update', $position->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')


                        <input type="hidden" name="backUrl" value="{{ $backUrl }}">
                        <input type="hidden" name="employeeId" value="{{ $employeeId }}">

                        <!-- Должность -->
                        <div>
                            <label for="position_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Должность *
                            </label>
                            <select name="position_id" id="position_id" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos->id }}"
                                        {{ $pos->id == $position->position_id ? 'selected' : '' }}>
                                        {{ $pos->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <!-- Ставка -->
                        <div>
                            <label for="rate" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Ставка *
                            </label>
                            <input type="text" name="rate" id="rate" placeholder="Введите ставку"
                                value="{{ $position->rate }}" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                        </div>

                        <!-- Комиссариат -->
                        <div>
                            <label for="commissariat_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Комиссариат *
                            </label>
                            <select name="commissariat_id" id="commissariat_id" required
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                @foreach ($commissariats as $commissariat)
                                    <option value="{{ $commissariat->id }}"
                                        {{ $commissariat->id == $position->commissariat_id ? 'selected' : '' }}>
                                        {{ $commissariat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Отдел -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Отдел
                            </label>
                            <select name="department_id" id="department_id"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                <option value="">Не выбран</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $department->id == $position->department_id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Отделение -->
                        <div>
                            <label for="division_id" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Отделение
                            </label>
                            <select name="division_id" id="division_id"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                <option value="">Не выбрано</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ $division->id == $position->division_id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                        @if ($division->department_id === null)
                                            (Самостоятельное отделение)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Самостоятельная должность -->
                        <div>
                            <label for="is_independent" class="block text-sm font-medium text-[#565A5B] mb-2">
                                Самостоятельная должность
                            </label>
                            <select name="is_independent" id="is_independent"
                                class="w-full px-4 py-3 bg-white border border-[#BFBFBF] rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] outline-none transition-colors text-[#060606]">
                                <option value="0" {{ $position->is_independent ? '' : 'selected' }}>Нет</option>
                                <option value="1" {{ $position->is_independent ? 'selected' : '' }}>Да</option>
                            </select>
                        </div>



                        <div class="flex items-center justify-between pt-4 border-t border-[#BFBFBF]">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Обновить
                            </button>
                        </div>
                    </form>

                    <form
                        action="{{ route('employee-positions.delete', [
                            'id' => $position->id,
                            'back_url' => $backUrl,
                        ]) }}"
                        method="POST" class="mt-0.5 inline-block"
                        onsubmit="return confirm('Вы уверены, что хотите удалить это назначение?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
