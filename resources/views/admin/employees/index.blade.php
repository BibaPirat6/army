@extends('layouts.main')

@section('header-title')
    Сотрудники
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif




    <div class="max-w-6xl p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#060606]">Сотрудники</h1>
                <p class="text-[#565A5B] mt-1">Список всех сотрудников системы</p>
            </div>
            <a href="{{ route('employees.create') }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать сотрудника
            </a>
        </div>

        <!-- Список сотрудников -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($employees as $employee)
                <div
                    class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden flex flex-col transition-all duration-200 hover:shadow-xl hover:-translate-y-1">
                    <div class="flex-1 p-6">
                        <!-- Основная информация -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-[#060606] mb-1">ID: {{ $employee->id }}</h3>
                                <p class="text-[#565A5B] text-sm mb-3">
                                    Статус: {{ $employee->workStatus->description ?? '—' }}
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                @if ($employee->user?->role?->name === 'user' || $employee->user?->role === null)
                                    <svg class="w-5 h-5 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                @endif
                            </div>
                        </div>


                        <!-- Пользовательская информация -->
                        <div class="mb-4 p-4 bg-white/50 rounded-lg border border-[#BFBFBF]">
                            <h4 class="font-semibold text-[#060606] mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ $employee->user->role->description ?? 'Пользователь' }}
                            </h4>
                            @if ($employee->user)
                                <ul class="text-sm text-[#565A5B] space-y-1">
                                    <li><span class="font-medium">ID:</span> {{ $employee->user->id }}</li>
                                    <li><span class="font-medium">Логин:</span> {{ $employee->user->login }}</li>
                                </ul>

                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#BFBFBF]">
                                    <a href="{{ route('users.show', [
                                        'id' => $employee->user->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}"
                                        class="inline-flex items-center px-3 py-1 bg-[#c0b6b9] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                        Подробнее
                                    </a>
                                    <a href="{{ route('users.edit', [
                                        'id' => $employee->user->id,
                                        'employee_id' => $employee->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}"
                                        class="inline-flex items-center px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                        Изменить
                                    </a>

                                    <form action="{{ route('users.delete', $employee->user->id) }}" method="post"
                                        class="inline-block"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить пользователя {{ $employee->user->login }}?');">
                                        @method('DELETE')
                                        @csrf
                                        <input type="hidden" name="backUrl" value="{{ route('employees.index') }}">
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-[#060606] text-white text-xs font-medium rounded hover:bg-[#060606]/80 transition-colors">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="text-[#7F7F7F] text-sm italic mb-3">Пользователь не указан</p>
                                <a href="{{ route('users.create', [
                                    'employee_id' => $employee->id,
                                    'back_url' => route('employees.index'),
                                ]) }}"
                                    class="inline-flex items-center px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                    Создать
                                </a>
                            @endif
                        </div>

                        <!-- Персональные данные -->
                        <div class="p-4 bg-white/50 rounded-lg border border-[#BFBFBF]">
                            <h4 class="font-semibold text-[#060606] mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                </svg>
                                Персональные данные
                            </h4>
                            @if ($employee->person)
                                <ul class="text-sm text-[#565A5B] space-y-1">
                                    <li><span class="font-medium">ID:</span> {{ $employee->person->id }}</li>
                                    <li><span class="font-medium">ФИО:</span>
                                        {{ $employee->person->last_name ?? '' }}
                                        {{ $employee->person->first_name ?? '' }}
                                        {{ $employee->person->patronymic ?? '' }}</li>
                                    <li><span class="font-medium">Телефон:</span>
                                        @foreach ($employee->person->phones ?? [] as $phone)
                                            <span>+{{ $phone }}</span>
                                        @endforeach
                                    </li>
                                    <li><span class="font-medium">Email:</span>
                                        @foreach ($employee->person->emails ?? [] as $email)
                                            <span>{{ $email }}</span>
                                        @endforeach
                                    </li>
                                </ul>

                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-[#BFBFBF]">
                                    <a href="{{ route('persons.show', [
                                        'id' => $employee->person->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}"
                                        class="inline-flex items-center px-3 py-1 bg-[#c0b6b9] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                        Подробнее
                                    </a>
                                    <a href="{{ route('persons.edit', [
                                        'id' => $employee->person->id,
                                        'employee_id' => $employee->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}"
                                        class="inline-flex items-center px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                        Изменить
                                    </a>

                                    <form action="{{ route('persons.delete', $employee->person->id) }}" method="post"
                                        class="inline-block"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить персональные данные {{ $employee->person->last_name ?? '' }} {{ $employee->person->first_name ?? '' }}?');">
                                        @method('DELETE')
                                        @csrf
                                        <input type="hidden" name="backUrl" value="{{ route('employees.index') }}">
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-[#060606] text-white text-xs font-medium rounded hover:bg-[#060606]/80 transition-colors">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="text-[#7F7F7F] text-sm italic mb-3">Персона не указана</p>
                                <a href="{{ route('persons.create', [
                                    'employee_id' => $employee->id,
                                    'back_url' => route('employees.index'),
                                ]) }}"
                                    class="inline-flex items-center px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                    Создать
                                </a>
                            @endif
                        </div>


                        {{-- должности сотрудника --}}
                        <div class="p-4 bg-white/50 rounded-lg border border-[#BFBFBF] mt-4">
                            <h4 class="font-semibold text-[#060606] mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Должности
                            </h4>
                            @if ($employee->positions->count() > 0)
                                <ul class="text-sm text-[#565A5B] space-y-1 mb-4">
                                    @foreach ($employee->positions as $position)
                                        <li><span class="font-medium">ID:</span> {{ $position->id }}
                                            {{ $position->position->name }} | Ставка {{ $position->rate }}</li>
                                    @endforeach
                                </ul>

                                <div class="grid grid-cols-2 gap-1">
                                    <a href="{{ route('employee-positions.create', [
                                        'id' => $employee->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}"
                                        class="inline-block px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors text-center">
                                        Создать
                                    </a>
                                    <a href="{{ route('employee-positions.show', [
                                        'id' => $employee->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}"
                                        class="inline-block px-3 py-1 bg-[#c0b6b9] text-white text-xs font-medium rounded hover:bg-[#c0b6b9]/80 transition-colors text-center">
                                        Подробнее
                                    </a>
                                    <a href="{{ route('employee-positions.edit', [
                                        'id' => $employee->id,
                                        'back_url' => route('employees.index'),
                                    ]) }}"
                                        class="inline-block px-3 py-1 bg-[#5a4a50] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors text-center">
                                        Изменить
                                    </a>
                                    <form
                                        action="{{ route('employee-positions.destroy', [
                                            'id' => $employee->id,
                                            'back_url' => route('employees.index'),
                                        ]) }}"
                                        method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-block w-full px-3 py-1 bg-[#060606] text-white text-xs font-medium rounded hover:bg-[#060606]/80 transition-colors"
                                            onclick="return confirm('Удалить все назначения для сотрудника?')">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="text-[#7F7F7F] text-sm italic mb-3">Не назначена должность</p>
                                <a href="{{ route('employee-positions.create', [
                                    'id' => $employee->id,
                                    'back_url' => route('employees.index'),
                                ]) }}"
                                    class="inline-flex items-center px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                    Создать
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="pt-4 border-t border-[#BFBFBF] mt-auto px-6 pb-6">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('employees.edit', $employee->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Редактировать
                            </a>

                            <form action="{{ route('employees.delete', $employee->id) }}" method="post"
                                class="inline-block"
                                onsubmit="return confirm('Вы уверены, что хотите удалить сотрудника?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 ml-2">
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
                </div>
            @empty
                <div class="py-12 text-center col-span-full">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        <p class="text-[#565A5B] text-lg font-medium">Нет сотрудников</p>
                        <p class="text-[#7F7F7F] mt-1">Создайте первого сотрудника для начала работы</p>
                    </div>
                </div>
            @endforelse
        </div>


        @include('includes.pagination', ['paginator' => $employees])
    </div>
@endsection
