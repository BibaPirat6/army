<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class BasicController extends Controller
{
    /**
     * Показать форму входа
     */
    public function index()
    {
        return view("static.login");
    }

    /**
     * Обработать попытку входа
     */
    public function login(Request $request)
    {
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
            Auth::login($user, $request->filled('remember'));

            // Регенерация сессии для безопасности
            $request->session()->regenerate();

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
