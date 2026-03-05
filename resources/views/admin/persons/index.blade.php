@extends('layouts.main')

@section('header-title')
    Персональные данные
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
            <div>
                <h1 class="text-2xl font-bold text-[#060606]">Персональные данные</h1>
                <p class="text-[#565A5B] mt-1">Список всех персональных данных сотрудников</p>
            </div>
            <a href="{{ route('persons.create') }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать
            </a>
            <a href="{{ route('persons-columns.index',[
                'back_url'=>url()->full()
            ]) }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создание полей
            </a>
        </div>

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-[#e7e1e1]">Фото</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-[#e7e1e1]">ФИО</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-[#e7e1e1]">Телефон</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-[#e7e1e1]">Email</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($persons as $person)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-3 py-2">
                                    @if ($person->photo)
                                        <div
                                            class="w-8 h-8 rounded-full overflow-hidden border border-[#565A5B] bg-[#060606]">
                                            <img src="{{ asset('storage/' . $person->photo) }}"
                                                alt="Фото {{ $person->last_name }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-[#BFBFBF] flex items-center justify-center">
                                            <svg class="w-4 h-4 text-[#565A5B]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-[#060606] text-xs">
                                    <div class="font-medium">{{ $person->last_name ?? '' }}</div>
                                    <div>{{ $person->first_name ?? '' }}</div>
                                    <div>{{ $person->patronymic ?? '' }}</div>
                                </td>
                                <td class="px-3 py-2 text-[#060606] text-xs">
                                    @if ($person->phones && count($person->phones) > 0)
                                        @foreach ($person->phones as $phone)
                                            <div>+{{ $phone }}</div>
                                        @endforeach
                                    @else
                                        <div>-</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-[#060606] text-xs">
                                    @if ($person->emails && count($person->emails) > 0)
                                        @foreach ($person->emails as $email)
                                            <div>{{ $email }}</div>
                                        @endforeach
                                    @else
                                        <div>-</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <div class="grid grid-cols-3 gap-0.5">
                                        <a href="{{ route('persons.show', $person->id) }}"
                                            class="inline-block px-2 py-1 bg-[#aa9fa3] text-white text-xs font-medium rounded hover:bg-[#aa9fa3]/80 transition-colors text-center">
                                            Подробнее
                                        </a>
                                        <a href="{{ route('persons.edit', $person->id) }}"
                                            class="inline-block px-2 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors text-center">
                                            Редакт.
                                        </a>
                                        <form action="{{ route('persons.delete', $person->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Удалить {{ $person->last_name }} {{ $person->first_name }}?');">
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
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-[#BFBFBF] mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-sm font-medium">Нет данных</p>
                                        <p class="text-[#7F7F7F] text-xs mt-1">Создайте первые персональные данные</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('includes.pagination', ['paginator' => $persons])
    </div>
@endsection
