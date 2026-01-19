<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Авторизация</title>
</head>

<body>
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if (session('success'))
        {{ session('success') }}
    @endif



    <h1>Войдите в систему</h1>

    <form action="{{ route('login.post') }}" method="POST">
        @csrf

        <label for="login">Логин*</label> <br>
        <input type="text" name="login" id="login" placeholder="Введите логин">
        <br>

        <label for="password">Пароль*</label> <br>
        <input type="password" name="password" id="password" placeholder="Введите пароль"> <br>

        <button type="submit">Войти</button>
    </form>
</body>

</html>
