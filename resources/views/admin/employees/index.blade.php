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


    <div class="w-full mx-auto p-4">
        <!-- кнопка создания -->
        <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-[#060606]">Сотрудники</h1>
                <p class="text-[#565A5B] text-sm">Список всех сотрудников системы</p>
            </div>

            <a href="{{ route('employees.create', [
                'back_url' => url()->full(),
            ]) }}"
                class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors shadow hover:shadow-md active:scale-[0.98]">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Создать сотрудника
            </a>
        </div>

        {{-- таблица --}}
        <div class="rounded-lg border border-[#BFBFBF]">
            <table class="min-w-full divide-y divide-[#BFBFBF] bg-[#e7e1e1] text-sm">
                {{-- шапка таблицы --}}
                <thead class="bg-[#d5cfcf]">
                    <tr>
                        <form method="GET" action="{{ route('employees.index') }}">
                            
                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">ID/статус</th>

                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Пользователь</th>
                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Персона (ФИО)
                            </th>

                            <th class="px-4 py-2 text-left text-[#060606] font-medium whitespace-nowrap">Должности
                            </th>

                            <th class="px-4 py-2 text-right text-[#060606] font-medium whitespace-nowrap">Действия</th>
                        </form>
                    </tr>
                </thead>

                @include('admin.employees.partials.table-body')
            </table>
        </div>

        @include('includes.pagination', ['paginator' => $employees])
    </div>
@endsection

