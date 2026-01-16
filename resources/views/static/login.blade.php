@extends('layouts.main')

@section('header-title')
    Вход в систему
@endsection

@section('content')
    <div style="max-width: 400px; margin: 50px auto; padding: 20px;">
        <h2 style="text-align: center; margin-bottom: 30px;">Вход в систему</h2>

        @if ($errors->any())
            <div style="background-color: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div style="margin-bottom: 20px;">
                <label for="login" style="display: block; margin-bottom: 5px; font-weight: bold;">Логин</label>
                <input 
                    type="text" 
                    id="login" 
                    name="login" 
                    placeholder="Введите логин" 
                    value="{{ old('login') }}"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;"
                    required
                    autofocus
                >
                @error('login')
                    <span style="color: #c33; font-size: 14px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Пароль</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Введите пароль"
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box;"
                    required
                >
                @error('password')
                    <span style="color: #c33; font-size: 14px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center;">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        {{ old('remember') ? 'checked' : '' }}
                        style="margin-right: 5px;"
                    >
                    <span>Запомнить меня</span>
                </label>
            </div>

            <button 
                type="submit" 
                style="width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;"
            >
                Войти
            </button>
        </form>
    </div>
@endsection
