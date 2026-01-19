<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::withoutTrashed()->get();

        return view("admin.users.index", ['users' => $users]);
    }

    public function create(Request $request)
    {
        $request->validate([
            "login" => "required|min:5|max:255|unique:users",
            "password" => "required|min:5|max:255",
        ], [
            "login.required" => "Логин обязателен",
            "login.min" => "Логин минимум 5 символов",
            "login.max" => "Логин максимум 255 символов",
            "login.unique" => "Логин уже занят",

            "password.required" => "Пароль обязателен",
            "password.min" => "Пароль минимум 5 символов",
            "password.max" => "Пароль максимум 255 символов",
        ]);

        $data = [
            "login" => $request["login"],
            "password_hash" => Hash::make($request["password"])
        ];

        $user = User::create($data);

        return redirect()->route("users.index")->with("success", "Пользователь " . $user->login . " успешно создан!");
    }
}
