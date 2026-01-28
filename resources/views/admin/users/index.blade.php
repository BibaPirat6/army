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


    <div class="max-w-6xl mx-auto p-6">
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

        <!-- Список пользователей -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($users as $user)
                <div
                    class="bg-[#e7e1e1] rounded-2xl shadow-lg border border-[#BFBFBF] overflow-hidden transition-all duration-200 hover:shadow-xl hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-[#060606] mb-2">{{ $user->login }}</h3>
                                <p class="text-[#565A5B] text-sm">
                                    Роль: {{ $user->role?->description ?? 'не назначена' }}
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-[#A60644]/10 flex items-center justify-center">
                                @if ($user->role->name === "user")
                                    <svg class="w-5 h-5 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-[#A60644]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                @endif

                            </div>
                        </div>

                        <div class="flex items-center justify-center pt-4 border-t border-[#BFBFBF]">
                            <a href="{{ route('users.edit', $user->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-[#A60644] text-white text-sm font-medium rounded-lg hover:bg-[#A60644]/80 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Изменить
                            </a>

                            <form action="{{ route('users.delete', $user->id) }}" method="post" class="inline-block"
                                onsubmit="return confirm('Вы уверены, что хотите удалить пользователя \"{{ $user->login }}\"?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-[#060606] text-white text-sm font-medium rounded-lg hover:bg-[#060606]/80 transition-colors duration-200 ml-2">
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
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-[#BFBFBF] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        <p class="text-[#565A5B] text-lg font-medium">Нет пользователей</p>
                        <p class="text-[#7F7F7F] mt-1">Создайте первого пользователя для начала работы</p>
                    </div>
                </div>
            @endforelse
        </div>

        @include('includes.pagination', ['paginator' => $users])
    </div>
@endsection
