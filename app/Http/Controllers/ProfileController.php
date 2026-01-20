<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        return view('profile.index')->with('employee', $employee);
    }

    public function updateShow()
    {
        $employee = auth()->user()->employee;

        return view('profile.update')->with('employee', $employee);
    }

    public function update(Request $request)
    {
        $employee = auth()->user()->employee;
        $request->validate([
            "login" => [
                "required",
                "string",
                "min:5",
                Rule::unique('users', 'login')->ignore($employee->user->id)
            ],
            "last_name" => "required|string|min:2",
            "first_name" => "required|string|min:2",
            "patronymic" => "nullable|string|min:2",
            'email' => [
                'nullable',
                'email',
                Rule::unique('persons', 'email')->ignore($employee->person->id)
            ],
            "phone" => [
                "nullable",
                "string",
                "min:10",
                Rule::unique('persons', 'phone')->ignore($employee->person->id)
            ],
            "photo" => "nullable|mimes:jpeg,png,jpg,gif|max:8192",
        ], [
            "login.required" => "Поле Логин обязательно для заполнения",
            "login.min" => "Поле Логин минимум 5 символов",
            "login.unique" => "Логин уже занят",
            "last_name.required" => "Поле Фамилия обязательно для заполнения",
            "last_name.min" => "Поле Фамилия минимум 2 символа",
            "first_name.required" => "Поле Имя обязательно для заполнения",
            "first_name.min" => "Поле Имя минимум 2 символа",
            "patronymic.required" => "Поле Отчество обязательно для заполнения",
            "patronymic.min" => "Поле Отчество минимум 2 символа",
            "email.email" => "Поле Почта должно быть действительным электронным адресом",
            "email.unique" => "Такой адрес Почты уже зарегистрирован",
            "phone.min" => "Поле Телефон минимум 10 символов",
            "phone.unique" => "Такой номер Телефона уже зарегистрирован",
            "photo.mimes" => "Файл Фото должен быть одного из следующих типов: jpeg, png, jpg, gif",
            "photo.max" => "Файл Фото не должен превышать размер 8 МБ",
        ]);

        $user = auth()->user();
        $user->update([
            'login' => $request->input('login'),
        ]);

        $personData = [
            'last_name' => $request->input('last_name'),
            'first_name' => $request->input('first_name'),
            'patronymic' => $request->input('patronymic'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ];

        if ($request->hasFile('photo')) {
            if ($employee->person->photo) {
                $oldPhotoPath = $employee->person->photo;
                if (Storage::disk('public')->exists($oldPhotoPath)) {
                    Storage::disk('public')->delete($oldPhotoPath);
                }
            }

            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $path = $request->file('photo')->storeAs('photos', $filename, 'public');

            $personData['photo'] = $path;
        }

        $employee->person->update($personData);

        return redirect()->route('profile.index')->with('success', 'Профиль успешно обновлен.');
    }
}
