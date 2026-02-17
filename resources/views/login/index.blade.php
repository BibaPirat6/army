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

    <div class="flex items-center justify-center min-h-screen bg-gray-100 p-4">
        <div class="w-full max-w-md p-6 bg-white border rounded-lg shadow-md">
            <h1 class="text-2xl font-bold text-center mb-4">Войдите в систему</h1>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="login" class="block mb-1 text-sm font-medium">Логин <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="login" id="login" placeholder="Введите логин"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-[#A60644]">
                </div>

                <div>
                    <label for="password" class="block mb-1 text-sm font-medium">Пароль <span
                            class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" placeholder="Введите пароль"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-[#A60644]">
                </div>

                <button type="submit"
                    class="w-full py-2 bg-[#A60644] text-white font-medium rounded hover:bg-[#7f0534] transition-colors">
                    Войти в систему
                </button>
            </form>
        </div>
    </div>

</body>

</html>
