<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class BasicController extends Controller
{
    /**
     * Показать форму входа
     */
    public function index(Request $request)
    {
        // Если пользователь уже авторизован, перенаправляем
        if (Auth::check()) {
            return redirect()->route('structure');
        }

        return view("static.login");
    }

    /**
     * Обработать попытку входа
     */
    public function login(Request $request)
    {
        // Если пользователь уже авторизован, перенаправляем
        if (Auth::check()) {
            return redirect()->route('structure');
        }

        // Валидация данных
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Поиск пользователя по логину
        $user = User::where('login', $credentials['login'])->first();

        // Проверка пароля
        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            // Авторизация пользователя
            Auth::login($user);

            // Регенерация сессии для безопасности
            $request->session()->regenerate();

            // Генерация токена для password_reset_tokens
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->login], // Используем login как email
                [
                    'email' => $user->login,
                    'token' => Hash::make($token),
                ]
            );

            // Перенаправление на страницу структуры
            return redirect()->intended(route('structure'));
        }

        // Если авторизация не удалась, возвращаем с ошибкой
        return back()->withErrors([
            'login' => 'Неверный логин или пароль.',
        ])->onlyInput('login');
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
