@extends('layouts.main')

@section('header-title')
    Штатные должности комиссариата
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif


    <div class="w-full p-6 mx-auto">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center mb-4">
                    <a href="{{ $backUrl ?? route('commissariats.index') }}"
                        class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Назад
                    </a>
                </div>
                <h1 class="text-2xl font-bold text-[#060606]">Штатные должности</h1>
            </div>
            <a href="{{ route('commissariat-positions.create', [
        'back_url' => url()->full(),
        'commissariat_id' => $commissariat->id
    ]) }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать штатную должность
            </a>
        </div>

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Должность</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Назначение</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($commissariatPositions as $pos)
                                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                                <td class="px-6 py-4 text-[#060606] font-medium"><a href="{{ route("positions.show", [
                                "id" => $pos->id,
                                "back_url" => url()->full()
                            ]) }}">{{ $pos->position->name }}</a>
                                                </td>
                                                <td class="px-6 py-4 text-[#060606] font-medium">
                                                    @if (!empty($pos->activeAssignment->employee))
                                                                            <a
                                                                                href="{{ route("employees.show", [
                                                            "id" => $pos->activeAssignment->employee->id,
                                                            "back_url" => url()->full()
                                                        ]) }}">{{ $pos->activeAssignment->employee->getFullNameAttribute() }}</a>
                                                    @else
                                                        <a href="/" class="text-[#901]">ВАКАНТ</a>
                                                    @endif
                                                </td>

                                                <td class="px-6 py-4 text-right">
                                                    <a href="{{ route('commissariat-positions.show', [
                                'id' => $pos->id,
                                'back_url' => url()->full(),
                                'commissariat_id' => $commissariat->id
                            ]) }}"
                                                        class="inline-flex items-center px-4 py-2 bg-[#446ca4] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                        подробнее
                                                    </a>

                                                    <form action="{{ route('commissariat-positions.delete', [
                                "id" => $pos->id,
                                "back_url" => url()->full()
                            ]) }}" method="POST" class="inline-block mt-0.5"
                                                        onsubmit="return confirm('Вы уверены, что хотите удалить штатную должность?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                            Удалить
                                                        </button>
                                                    </form>
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
                                        <p class="text-[#565A5B] text-lg font-medium">Нет штата</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    </div>
@endsection