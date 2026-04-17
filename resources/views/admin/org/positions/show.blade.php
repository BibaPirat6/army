@extends('layouts.main')

@section('header-title')
    {{ $position['name'] }}
@endsection

@section('content')
    <div class="max-w-2xl p-6 mx-auto">
        <div class="flex items-center mb-4">
            <a href="{{ $backUrl ?? route('positions.index') }}"
                class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Назад
            </a>
        </div>

        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-[#060606]">{{ $position['name'] }}</h1>
                    <p class="text-[#565A5B] mt-1">Детали должности</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">ID</span>
                        <span class="text-[#060606]">{{ $position['id'] }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Тип должности</span>
                        <span class="text-[#060606]">{{ $position->getPositionTypeNameAttribute() }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Тип начальства</span>
                        <span class="text-[#060606]">{{ $position->getChiefTypeNameAttribute() }}</span>
                    </div>

                    <!-- Контейнер для кнопок -->
                    <div class="flex w-full gap-2">
                        <!-- gap-2 добавляет небольшой отступ между кнопками, можно убрать или изменить -->

                        <!-- Редактировать -->
                        <a href="{{ route('positions.edit', [
                            "id"=>$position->id,
                            "back_url"=>url()->full()
                        ]) }}"
                            class="group/action flex-1 p-2 rounded-lg bg-[#A60644] text-[#fff] hover:bg-[#A60644] hover:text-white transition-all duration-200 text-center"
                            title="Редактировать">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Редактировать
                        </a>

                        <!-- Удалить -->
                        <form action="{{ route('positions.delete', [
                            "id"=>$position->id,
                            "back_url"=>url()->full()
                        ]) }}" method="POST"
                            onsubmit="return confirm('Вы уверены, что хотите удалить должность «{{ $position->name }}»?');"
                            class="flex-1 inline-block"> <!-- flex-1 заставит форму занять оставшееся место (50%) -->
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full group/action p-2 rounded-lg bg-red-600 text-[#fff] hover:bg-[#060606] hover:text-white transition-all duration-200"
                                title="Удалить">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Удалить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection