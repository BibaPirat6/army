<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);

        return view("admin.users.index")->with("users", $users);
    }
    public function create(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $backUrl = $request->get('back_url');
        $decodedBackUrl = urldecode($backUrl);

        $roles = Role::all();
        return view('admin.users.create', compact('roles', 'employeeId', 'decodedBackUrl'));
    }

    public function store(Request $request)
    {
        $request->validate([
            "login" => "required|min:5|max:255|unique:users",
            "password" => "required|min:5|max:255",
            "role" => "required|exists:roles,id",
            "employeeId" => "nullable|integer|min:1|exists:employees,id"
        ], [
            "login.required" => "Логин обязателен",
            "login.min" => "Логин минимум 5 символов",
            "login.max" => "Логин максимум 255 символов",
            "login.unique" => "Логин уже занят",

            "password.required" => "Пароль обязателен",
            "password.min" => "Пароль минимум 5 символов",
            "password.max" => "Пароль максимум 255 символов",

            "role.required" => "Роль обязательна",
            "role.exists" => "Недопустимое значение для роли",

            "employeeId.exists" => "Несуществующий id сотрудника"
        ]);

        $backUrl = $request->input('decodedBackUrl');
        $employeeId = $request->input("employeeId");


        $data = [
            "login" => $request["login"],
            "password_hash" => Hash::make($request["password"]),
            "role_id" => $request["role"]
        ];

        $user = User::create($data);

        if ($employeeId) {
            $employee = Employee::findOrFail($employeeId);
            $employee->update(["user_id" => $user->id]);
        }

        return redirect($backUrl ?? route("users.index"))->with("success", "Пользователь " . $user->login . " успешно создан!");

    }



    public function edit(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();

        $employeeId = $request->get('employee_id');
        $backUrl = $request->get('back_url');
        $decodedBackUrl = urldecode($backUrl);

        return view('admin.users.edit')->with(['user' => $user, 'roles' => $roles, 'employeeId' => $employeeId, "decodedBackUrl" => $decodedBackUrl]);
    }

    public function update(Request $request, $id)
    {
        if (!$request->filled('login') && !$request->filled('password') && !$request->filled('role')) {
            return back()->withErrors([
                'error' => 'Заполните хотя бы одно поле: логин, пароль или роль'
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

        if ($request->filled('role')) {
            $validationRules['role'] = "required|exists:roles,id";
            $validationMessages['role.required'] = "Роль обязательна";
            $validationMessages['role.exists'] = "Недопустимое значение для роли";
        }

        if ($request->filled('employeeId')) {
            $validationRules['employeeId'] = "nullable|integer|min:1|exists:employees,id";
            $validationMessages['employeeId.exists'] = "Недопустимое значение для сотрудника id";
        }

        $request->validate($validationRules, $validationMessages);

        $data = [];

        if ($request->filled('login')) {
            $data['login'] = $request->login;
        }

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        if ($request->filled('role')) {
            $data['role_id'] = $request->role;
        }

        $user = User::findOrFail($id);

        $user->update($data);

        $backUrl = $request->input("decodedBackUrl");

        return redirect($backUrl ?? route("users.index"))
            ->with("success", "Пользователь обновлен!");
    }


    public function delete(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        $backUrl = $request->input("backUrl");

        return redirect($backUrl ?? route("users.index"))
            ->with('success', 'Пользователь удален');
    }
}
