@extends('layouts.main')

@section('header-title')
    Пользователи
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
                <h1 class="text-2xl font-bold text-[#060606]">Пользователи</h1>
                <p class="text-[#565A5B] mt-1">Список всех пользователей системы</p>
            </div>
            <a href="{{ route('users.create') }}"
                class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200 shadow-lg hover:shadow-xl active:scale-[0.98]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Создать пользователя
            </a>
        </div>

        <!-- Таблица -->
        <div class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#565A5B]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#e7e1e1]">Логин</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#e7e1e1]">Роль</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-[#e7e1e1]">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BFBFBF]">
                        @forelse($users as $user)
                            <tr class="hover:bg-[#A60644]/5 transition-colors duration-200">
                                <td class="px-4 py-3 text-[#060606] font-medium text-sm">
                                    {{ $user->login }}
                                </td>
                                <td class="px-4 py-3 text-[#060606] text-sm">
                                    <span class="inline-flex items-center">
                                        @if ($user->role->name === 'user')
                                            <svg class="w-4 h-4 mr-2 text-[#A60644]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-2 text-[#A60644]" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                        @endif
                                        {{ $user->role?->description ?? 'не назначена' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="grid grid-cols-3 gap-1">
                                        <a href="{{ route('users.show', $user->id) }}"
                                            class="inline-block px-3 py-1 bg-[#ab9da2] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors text-center">
                                            Подробнее
                                        </a>
                                        <a href="{{ route('users.edit', $user->id) }}"
                                            class="inline-block px-3 py-1 bg-[#A60644] text-white text-xs font-medium rounded hover:bg-[#A60644]/80 transition-colors text-center">
                                            Изменить
                                        </a>
                                        <form action="{{ route('users.delete', $user->id) }}" method="post"
                                            class="inline-block"
                                            onsubmit="return confirm('Удалить пользователя \"{{ $user->login }}\"?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-block w-full px-3 py-1 bg-[#060606] text-white text-xs font-medium rounded hover:bg-[#060606]/80 transition-colors">
                                                Удалить
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
                                        <p class="text-[#565A5B] text-sm font-medium">Нет пользователей</p>
                                        <p class="text-[#7F7F7F] text-xs mt-1">Создайте первого пользователя</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('includes.pagination', ['paginator' => $users])
    </div>
@endsection
