@extends('layouts.main')

@section('header-title')
    Отделы
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <div class="w-full p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#060606]">Отделы</h1>
                <p class="text-[#565A5B] mt-1">Список всех отделов</p>
            </div>
            <a href="{{ route('departments.create', [
        'back_url' => route('departments.index'),
    ]) }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Добавить отдел
            </a>
        </div>



        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Название</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Начальник</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Военкомат</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($departments as $department)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-6 py-4 text-[#060606] font-medium">{{ $department->id }}</td>
                                <td class="px-6 py-4 text-[#060606]">{{ $department->name }}</td>


                                <td class="px-6 py-4">
                                    @if ($department->chiefEmployeePosition !== null)
                                        @if ($department->chiefEmployeePosition->employee && $department->chiefEmployeePosition->employee->person)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $department->chiefEmployeePosition->employee->person->last_name ?? '*' }}
                                                {{ $department->chiefEmployeePosition->employee->person->first_name ?? '*' }}
                                                {{ $department->chiefEmployeePosition->employee->person->patronymic ?? '*' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Без ФИО (ID:
                                                {{ $department->chiefEmployeePosition->employee->id }})</span>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Нет
                                        </span>
                                    @endif
                                </td>


                                <td class="px-6 py-4">
                                    @if ($department->commissariat_id !== null)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $department->commissariat->name }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Нет
                                        </span>
                                    @endif
                                </td>

                                {{-- кнопки --}}
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <!-- Подробнее -->
                                        <a href="{{ route('departments.show', ['id' => $department->id, 'back_url' => url()->full()]) }}"
                                            class="inline-flex items-center px-3.5 py-1.5 bg-[#746c6f] text-white text-sm font-medium rounded-lg hover:bg-[#746c6f]/85 transition-colors duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Подробнее
                                        </a>

                                        <!-- Редактировать -->
                                        <a href="{{ route('departments.edit', ['id' => $department->id, 'back_url' => url()->full()]) }}"
                                            class="inline-flex items-center px-3.5 py-1.5 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/85 transition-colors duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Редактировать
                                        </a>

                                        <!-- Удалить -->
                                        <form action="{{ route('departments.delete', $department->id) }}" method="POST"
                                            onsubmit="return confirm('Вы уверены, что хотите удалить отдел \'{{ $department->name }}\'?');"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3.5 py-1.5 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/85 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-lg font-medium">Нет отделов</p>
                                        <p class="text-[#7F7F7F] mt-1">Создайте первый отдел для начала работы</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('includes.pagination', ['paginator' => $departments])
    </div>
@endsection