@extends('layouts.main')

@section('header-title')
    {{ $division['name'] }}
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif

    <div class="max-w-2xl p-6 mx-auto">
        {{-- кнопка назад --}}
        <div class="flex items-center mb-4">
            <a href="{{ $backUrl ?? route('divisions.index') }}"
                class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Назад
            </a>
        </div>

        {{-- данные --}}
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="p-6 md:p-8">

                <div class="space-y-4">

                    <h2 class="text-2xl font-bold text-[#060606] border-l-4 border-[#A60644] pl-4 py-1">
                        {{ $division['name'] }}
                    </h2>
                    {{-- данные отдела --}}
                    <div class="p-4 space-y-3 animate-fadeIn">
                        <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                            <span class="font-medium text-[#565A5B]">ID</span>
                            <span class="text-[#060606]">{{ $division['id'] }}</span>
                        </div>

                        {{-- Начальник --}}
                        <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                            <span class="font-medium text-[#565A5B]">Начальник</span>
                            @php
                                $chief = $division->getChiefAttribute();
                            @endphp
                            @if($chief && $chief->id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <a href="{{ route('employees.show', ['id' => $chief->id, 'back_url' => url()->full()]) }}">
                                        {{ $chief->getFullNameAttribute() }}
                                    </a>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    Не назначен
                                </span>
                            @endif
                        </div>

                        {{-- Комиссариат --}}
                        <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                            <span class="font-medium text-[#565A5B]">Комиссариат</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <a href="{{ route('commissariats.show', ['id' => $division->commissariat->id, 'back_url' => url()->full()]) }}">
                                    {{ $division->commissariat->name ?? 'Не указан' }}
                                </a>
                            </span>
                        </div>

                        {{-- Отдел --}}
                        <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                            <span class="font-medium text-[#565A5B]">Отдел</span>
                            @if (isset($division->department) && $division->department && $division->department->id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <a href="{{ route('departments.show', ['id' => $division->department->id, 'back_url' => url()->full()]) }}">
                                        {{ $division->department->name }}
                                    </a>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    Не указан
                                </span>
                            @endif
                        </div>

                        {{-- Кнопки действий --}}
                        <div class="pt-2">
                            <a href="{{ route('divisions.edit', ['id' => $division->id, 'back_url' => url()->full()]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Редактировать отдел
                            </a>
                            <form action="{{ route('divisions.delete', $division->id) }}" method="POST"
                                class="inline-block mt-0.5"
                                onsubmit="return confirm('Вы уверены, что хотите удалить отдел \'{{ $division->name }}\'?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="backUrl" value="{{ $backUrl ?? route('divisions.index') }}">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    Удалить отдел
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection