@extends('layouts.main')

@section('header-title')
    {{ $commissariat['name'] }}
@endsection

@section('content')
    <div class="max-w-2xl p-6 mx-auto">
        <div class="flex items-center mb-4">
            <a href="{{ route('commissariats.index') }}"
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
                    <h1 class="text-2xl font-bold text-[#060606]">{{ $commissariat['name'] }}</h1>
                    <p class="text-[#565A5B] mt-1">Детали комиссариата</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">ID</span>
                        <span class="text-[#060606]">{{ $commissariat['id'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Начальник</span>



                        <span class="text-[#060606]">
                            @if ($commissariat->chiefEmployeePosition !== null)
                                @if ($commissariat->chiefEmployeePosition->employee && $commissariat->chiefEmployeePosition->employee->person)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $commissariat->chiefEmployeePosition->employee->person->last_name ?? '*' }}
                                        {{ $commissariat->chiefEmployeePosition->employee->person->first_name ?? '*' }}
                                        {{ $commissariat->chiefEmployeePosition->employee->person->patronymic ?? '*' }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Без ФИО (ID:
                                        {{ $commissariat->chiefEmployeePosition->id }})</span>
                                @endif
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Нет
                                </span>
                            @endif
                        </span>
                    </div>


                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Создан</span>
                        <span
                            class="text-[#060606]">{{ \Carbon\Carbon::parse($commissariat['created_at'])->format('d.m.Y H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-[#BFBFBF] last:border-b-0">
                        <span class="font-medium text-[#565A5B]">Обновлен</span>
                        <span
                            class="text-[#060606]">{{ \Carbon\Carbon::parse($commissariat['updated_at'])->format('d.m.Y H:i') }}</span>
                    </div>




                    {{-- можно бы тут сделать кнопки + сделать back_url --}}
                    <div>
                        <a href="{{ route('commissariats.edit', [
                            'id' => $commissariat->id,
                            'back_url' => back(),
                        ]) }}"
                            class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Редактировать
                        </a>
                        <form action="{{ route('commissariats.delete', $commissariat->id) }}" method="POST"
                            class="inline-block mt-0.5"
                            onsubmit="return confirm('Вы уверены, что хотите удалить комиссариат \"{{ $commissariat->name }}\"?');">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
