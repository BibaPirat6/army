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

        return view("admin.users.index")->with("users", $users);
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

    public function delete($id)
    {
        $res = User::where('id', $id)->delete();

        if ($res) {
            return redirect()->route('users.index')
                ->with('success', 'Пользователь удален');
        }

        return redirect()->back()
            ->with('error', 'Не удалось удалить пользователя');
    }

    public function updateShow($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.update')->with("user", $user);
    }

    public function update(Request $request, $id)
    {
        if (!$request->filled('login') && !$request->filled('password')) {
            return back()->withErrors([
                'error' => 'Заполните хотя бы одно поле: логин или пароль'
            ])->withInput();
        }

        $validationRules = [];
        $validationMessages = [];

        if ($request->filled('login')) {
            $validationRules['login'] = "required|min:5|max:255|unique:users,login," . $id;
            $validationMessages['login.required'] = "Логин обязателен";
            $validationMessages['login.min'] = "Логин минимум 5 символов";
            $validationMessages['login.max'] = "Логин максимум 255 символов";
            $validationMessages['login.unique'] = "Логин уже занят";
        }

        if ($request->filled('password')) {
            $validationRules['password'] = "required|min:5|max:255";
            $validationMessages['password.required'] = "Пароль обязателен";
            $validationMessages['password.min'] = "Пароль минимум 5 символов";
            $validationMessages['password.max'] = "Пароль максимум 255 символов";
        }

        $request->validate($validationRules, $validationMessages);

        $data = [];

        if ($request->filled('login')) {
            $data['login'] = $request->login;
        }

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $user = User::findOrFail($id);

        $user->update($data);

        return redirect()->route("users.index")
            ->with("success", "Пользователь обновлен!");
    }
}
