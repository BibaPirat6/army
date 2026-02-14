<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Вход в систему</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/android-chrome-192x192.png') }}">
    <!-- Theme color -->
    <meta name="msapplication-TileColor" content="#A60644">
    <meta name="theme-color" content="#ffffff">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @if (session('success'))
        @include('includes.success', ['success' => session('success')])
    @endif
    @if ($errors->any())
        @include('includes.errors', ['errors' => $errors])
    @endif

    <div class="flex items-center justify-center min-h-screen p-4 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="w-full max-w-md p-10 space-y-8 bg-white border border-gray-100 shadow-xl rounded-2xl">
            <div class="text-center">
                <h1 class="mb-2 text-3xl font-bold text-gray-900">Войдите в систему</h1>
                <p class="text-gray-600">Введите свои учетные данные для доступа</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="mt-8 space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="login" class="block text-sm font-medium text-gray-700">
                        Логин
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" name="login" id="login"
                            class="pl-10 w-full px-4 py-3 border border-gray-400 rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#8d2f53] transition-all duration-200 
                                  placeholder-gray-400 focus:outline-none focus:shadow-outline"
                            placeholder="Введите логин">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Пароль
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password"
                            class="pl-10 w-full px-4 py-3 border border-gray-400 rounded-lg focus:ring-2 focus:ring-[#A60644] focus:border-[#A60644] transition-all duration-200
                                  placeholder-gray-400 focus:outline-none focus:shadow-outline"
                            placeholder="Введите пароль">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent rounded-lg
                               text-white font-medium bg-gradient-to-r from-[#A60644] to-[#7f0534]
                               hover:from-[#7f1a40] hover:to-[#a7174e] focus:outline-none focus:ring-2 
                               focus:ring-offset-2 focus:ring-[#c71257] transition-all duration-200 
                               transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-white group-hover:text-blue-200" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </span>
                        Войти в систему
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
