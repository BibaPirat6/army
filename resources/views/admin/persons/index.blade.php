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


    <div class="max-w-6xl mx-auto p-6">
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
                Создать персональные данные
            </a>
        </div>

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Фото</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Фамилия</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Имя</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Отчество</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Телефон</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#e7e1e1]">Email</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($persons as $person)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-6 py-4">
                                    @if ($person->photo)
                                        <div
                                            class="w-12 h-12 rounded-full overflow-hidden border-2 border-[#565A5B] bg-[#060606]">
                                            <img src="{{ asset('storage/' . $person->photo) }}"
                                                alt="Фото {{ $person->last_name }}" class="w-full h-full object-cover">
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-[#BFBFBF] flex items-center justify-center">
                                            <svg class="w-6 h-6 text-[#565A5B]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-[#060606] font-medium">{{ $person->last_name }}</td>
                                <td class="px-6 py-4 text-[#060606]">{{ $person->first_name }}</td>
                                <td class="px-6 py-4 text-[#060606]">{{ $person->patronymic }}</td>
                                <td class="px-6 py-4 text-[#060606] font-mono">
                                    @foreach ($person->phones ?? [] as $phone)
                                        <div>+{{ $phone }}</div>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-[#060606] font-mono text-sm">
                                    @foreach ($person->emails ?? [] as $email)
                                        <div>{{ $email }}</div>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('persons.edit', $person->id) }}"
                                        class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Редактировать
                                    </a>
                                    <form class="mt-0.5" action="{{ route('persons.delete', $person->id) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Вы уверены, что хотите удалить персональные данные {{ $person->last_name }} {{ $person->first_name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
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
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        <p class="text-[#565A5B] text-lg font-medium">Нет персональных данных</p>
                                        <p class="text-[#7F7F7F] mt-1">Создайте первые персональные данные для начала работы
                                        </p>
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
