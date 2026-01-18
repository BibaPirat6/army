<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Вход в систему</title>
</head>

<body>
    <h1>Вход в систему</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.login') }}" method="POST">
        @csrf

        <div>
            <label for="login">Логин</label> <br>
            <input type="text" placeholder="Введите логин" id="login" name="login" value="{{ old('login') }}">
        </div>

        <br>

        <div>
            <label for="pwd">Пароль</label> <br>
            <input type="password" placeholder="Введите пароль" id="pwd" name="pwd">
        </div>

        <br>

        <button type="submit">Войти</button>
    </form>
</body>

</html>
