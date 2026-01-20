<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index()
    {
        return view("login.index");
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            "login" => "required",
            "password" => "required",
        ], [
            "login.required" => "Заполните поле логина",
            "password.required" => "Заполните поле пароля"
        ]);

        $user = User::where("login", $data["login"])->first();

        if (!$user || !Hash::check($data["password"], $user->password_hash)) {
            return back()->withErrors([
                "error" => "Неправильно введены данные"
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route("home.index")
            ->with("success", "Привет " . $user->login . "!");
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route("login")->with("success", "Вы вышли из аккаунта!");
    }
}
