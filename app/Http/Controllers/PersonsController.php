<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Storage;

class PersonsController extends Controller
{
    public function index()
    {
        $persons = Person::all();
        return view('admin.persons.index')->with('persons', $persons);
    }

    public function create(Request $request)
    {
        $request->validate([
            "last_name" => "required|string|min:2",
            "first_name" => "required|string|min:2",
            "patronymic" => "nullable|string|min:2",
            'email' => [
                'nullable',
                'email',
                Rule::unique('persons', 'email')
            ],
            "phone" => [
                "nullable",
                "string",
                "min:10",
                Rule::unique('persons', 'phone')
            ],
            "photo" => "nullable|mimes:jpeg,png,jpg,gif|max:8192",
        ], [
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

        $personData = [
            'last_name' => $request->input('last_name'),
            'first_name' => $request->input('first_name'),
            'patronymic' => $request->input('patronymic'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ];

        if ($request->hasFile('photo')) {
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $path = $request->file('photo')->storeAs('photos', $filename, 'public');

            $personData['photo'] = $path;
        }

        Person::create($personData);

        return redirect()->route('persons.index')->with('success', 'Персональные данные созданы!');
    }

    public function delete($id)
    {
        $person = Person::findOrFail($id);
        $person->delete();

        return redirect()->back()->with('success', 'Персональные данные удалены!');
    }

    public function updateShow($id)
    {
        $person = Person::findOrFail($id);
        return view('admin.persons.update')->with('person', $person);
    }

    public function update(Request $request, $id)
    {
        $person = Person::findOrFail($id);
        $request->validate([
            "last_name" => "required|string|min:2",
            "first_name" => "required|string|min:2",
            "patronymic" => "nullable|string|min:2",
            'email' => [
                'nullable',
                'email',
                Rule::unique('persons', 'email')->ignore($person->id)
            ],
            "phone" => [
                "nullable",
                "string",
                "min:10",
                Rule::unique('persons', 'phone')->ignore($person->id)
            ],
            "photo" => "nullable|mimes:jpeg,png,jpg,gif|max:8192",
        ], [
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

        $personData = [
            'last_name' => $request->input('last_name'),
            'first_name' => $request->input('first_name'),
            'patronymic' => $request->input('patronymic'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ];

        if ($request->hasFile('photo')) {
            if ($person->photo) {
                $oldPhotoPath = $person->photo;
                if (Storage::disk('public')->exists($oldPhotoPath)) {
                    Storage::disk('public')->delete($oldPhotoPath);
                }
            }

            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $path = $request->file('photo')->storeAs('photos', $filename, 'public');

            $personData['photo'] = $path;
        }

        $person->update($personData);

        return redirect()->route('persons.index')->with('success', 'Персональные данные успешно обновлены.');
    }
}
