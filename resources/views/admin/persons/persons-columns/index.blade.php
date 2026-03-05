@extends('layouts.main')

@section('header-title')
    Создание персональных данных
@endsection

@section('content')
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif


    <div class="max-w-4xl mx-auto p-6">
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <div class="flex items-center mb-4">
                <a href="{{ $backUrl ?? route('persons.index') }}"
                    class="inline-flex items-center text-[#A60644] font-medium hover:text-[#A60644]/80 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Назад
                </a>
            </div>

            <a href="{{ route('persons-columns.create', [
                'back_url' => url()->full(),
            ]) }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создание полей
            </a>
        </div>

        {{-- твблица --}}
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-[#e7e1e1]">Колонка</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @foreach ($columns as $col)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-3 py-2 text-[#060606] text-xs">
                                    {{ $col }}
                                </td>
                                <td>
                                    <div class="grid grid-cols-2 gap-0.5">
                                        <a href="{{ route('persons.edit', 1) }}"
                                            class="inline-block w-full text-center px-2 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors">
                                            Редактировать
                                        </a>
                                        <form action="{{ route('persons.delete', 1) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Удалить?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-block w-full px-2 py-1 bg-[#060606] text-white text-xs font-medium rounded hover:bg-[#060606]/80 transition-colors">
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
