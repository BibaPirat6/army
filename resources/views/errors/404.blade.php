@extends('layouts.main')

@section('header-title')
    Ошибка {{ $errorCode }}
@endsection

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-8">
        <div class="max-w-md w-full text-center">
            <!-- Иконка / иллюстрация -->
            <div class="mb-8">
                <svg class="w-32 h-32 mx-auto text-[#A60644]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <path d="M12 8v4m0 3v1" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>

            <!-- Код ошибки -->
            <h1 class="text-7xl font-extrabold text-[#060606] tracking-tight mb-2">404</h1>

            <!-- Сообщение -->
            <p class="text-lg text-gray-700 mb-8 leading-relaxed">
                {{ $title ?? 'Страница не найдена' }}
            </p>

            <!-- Кнопка "Домой" -->
            @if ($homeLink ?? false)
                <a href="{{ url('/') }}"
                    class="inline-flex items-center px-6 py-3 bg-[#A60644] text-white font-medium rounded-lg hover:bg-[#A60644]/85 transition-all duration-200 shadow-md hover:shadow-lg active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-[#A60644]/30">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3M9 21V9m0 0H5m4 12l4-4" />
                    </svg>
                    Вернуться на главную
                </a>
            @endif

            <!-- Дополнительная подсказка (необязательно) -->
            <p class="mt-6 text-sm text-gray-500">
                Возможно, ссылка устарела или страница была перемещена.
            </p>
        </div>
    </div>
@endsection
