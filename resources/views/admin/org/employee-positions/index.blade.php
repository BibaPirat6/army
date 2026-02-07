@extends('layouts.main')

@section('header-title')
    Назначение должностей сотрудникам
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif


    <div class="max-w-4xl mx-auto p-6">
        <!-- Заголовок -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-[#060606] mb-4">Назначение должностей сотрудникам</h1>
        </div>

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#e7e1e1]">Сотрудник</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#e7e1e1]">Должности</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-4 py-3 text-[#060606]">
                                    <div class="font-medium text-sm">
                                        {{ $employee->person->last_name ?? '' }}
                                        {{ $employee->person->first_name ?? '' }}
                                        {{ $employee->person->patronymic ?? '' }}
                                    </div>
                                    <div class="text-xs text-[#565A5B] mt-1">
                                        @foreach ($employee->person->phones ?? [] as $phone)
                                            <span>+{{ $phone }} |</span>
                                        @endforeach
                                    </div>
                                    <div class="text-xs text-[#565A5B]">
                                         @foreach ($employee->person->emails ?? [] as $email)
                                            <span>{{ $email }} |</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-[#060606]">
                                    @forelse($employee->positions as $employeePosition)
                                        <div class="bg-white/50 rounded border border-[#BFBFBF] p-2 mb-1 last:mb-0">
                                            <div class="text-xs">
                                                <span class="font-medium">{{ $employeePosition->position->name }}</span>
                                                <span class="ml-2">Ставка: {{ $employeePosition->rate }}</span>
                                            </div>
                                            <div class="text-xs text-[#565A5B] mt-1">
                                                <span title="комиссариат">{{ $employeePosition->commissariat->name }}</span>
                                                @if (isset($employeePosition->department->name))
                                                    > <span title="отдел">{{ $employeePosition->department->name }}</span>
                                                @endif
                                                @if (isset($employeePosition->division->name))
                                                    > <span title="отделение">{{ $employeePosition->division->name }}</span>
                                                @endif
                                                @if ($employeePosition->is_independent !== false)
                                                    <i
                                                        style="color: rgb(17, 183, 17)">({{ $employeePosition->is_independent ? 'Самостоятельная должность' : '' }})</i>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-[#7F7F7F] text-xs italic">Нет должностей</div>
                                    @endforelse
                                </td>
                                <td class="px-4 py-3 text-[#060606]">
                                    <div class="grid grid-cols-2 gap-1">
                                        <a href="{{ route('employee-positions.show', $employee->id) }}"
                                            class="inline-block px-3 py-1 bg-[#746c6f] text-white text-xs font-medium rounded hover:bg-[#746ccc]/80 transition-colors text-center">
                                            Подробнее
                                        </a>
                                        <a href="{{ route('employee-positions.create', $employee->id) }}"
                                            class="inline-block px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors text-center">
                                            Назначить
                                        </a>
                                        <a href="{{ route('employee-positions.edit', $employee->id) }}"
                                            class="inline-block px-3 py-1 bg-[#4a818e] text-white text-xs font-medium rounded hover:bg-[#4a8186]/80 transition-colors text-center">
                                            Редакт.
                                        </a>
                                        <form action="{{ route('employee-positions.destroy', $employee->id) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Удалить все назначения для {{ $employee->person->last_name ?? '' }} {{ $employee->person->first_name ?? '' }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-block w-full px-3 py-1 bg-[#060606] text-white text-xs font-medium rounded hover:bg-[#060606]/80 transition-colors">
                                                Удалить все
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-[#BFBFBF] mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-sm font-medium">Нет сотрудников</p>
                                        <p class="text-[#7F7F7F] text-xs mt-1">Создайте сотрудников для начала работы</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('includes.pagination', ['paginator' => $employees])
    </div>
@endsection
