<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        return view("login");
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'pwd' => 'required|string',
        ], [
            'login.required' => 'Поле логин обязательно для заполнения.',
            'pwd.required' => 'Поле пароль обязательно для заполнения.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('user.index')
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = [
            'login' => $request->input('login'),
            'password_hash' => $request->input('pwd'),
        ];

        $user = User::where('login', $credentials['login'])->first();

        if ($user && Hash::check($credentials['password_hash'], $user->password_hash)) {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            return redirect()->route('home.index');
        }

        return redirect()->route('user.index')
            ->withErrors(['login' => 'Неверный логин или пароль.'])
            ->withInput();
    }
}
