<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view("auth.index");
    }

    public function auth(Request $request)
    {
        $data = $request->validate([
            "login" => "required",
            "password" => "required",
        ], [
            "login.required" => "Заполните поле логина",
            "password.required" => "Заполните поле пароля"
        ]);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();
            
            return "123";
        }
        return back()->withErrors([
            "error" => "Неправильно введены данные"
        ]);
    }
}
