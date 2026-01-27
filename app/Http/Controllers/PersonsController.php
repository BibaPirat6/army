<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Storage;

class PersonsController extends Controller
{
    public function index()
    {
        $persons = Person::paginate(10);
        return view('admin.persons.index')->with('persons', $persons);
    }

    public function create(Request $request)
    {
        $backUrl = $request->get("back_url");
        $employeeId = $request->get("employee_id");

        return view("admin.persons.create", compact("backUrl", "employeeId"));
    }

    public function store(Request $request)
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
            "employeeId" => "nullable|integer|min:1|exists:employees,id"
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
            "employeeId.exists" => "Не существующий id сотрудника"
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

        $person = Person::create($personData);

        $backUrl = $request->input("backUrl");
        $employeeId = $request->input("employeeId");

        if ($employeeId) {
            $employee = Employee::findOrFail($employeeId);
            $employee->update(["person_id" => $person->id]);
        }

        return redirect($backUrl ?? route("persons.index"))->with('success', 'Персональные данные созданы!');
    }


    public function edit(Request $request, $id)
    {
        $person = Person::findOrFail($id);

        $backUrl = $request->input("back_url");

        return view('admin.persons.edit', compact("person", "backUrl"));
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
            "photo" => "nullable|mimes:jpeg,png,jpg,gif|max:8192"
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
                Storage::disk('public')->delete($person->photo);
            }

            $file = $request->file('photo');

            $manager = new ImageManager(new Driver());

            $image = $manager->read($file);

            $image->scale(width: 150);

            $filename = time() . '.webp';
            $path = 'photos/' . $filename;

            $webp = $image->encode(new WebpEncoder(quality: 75));

            Storage::disk('public')->put($path, $webp);

            $personData['photo'] = $path;
        }

        $person->update($personData);


        $backUrl = $request->input("backUrl");

        return redirect($backUrl ?? route("persons.index"))->with('success', 'Персональные данные успешно обновлены.');
    }

    public function delete(Request $request, $id)
    {
        $person = Person::findOrFail($id);
        $person->delete();

        $backUrl = $request->input("backUrl");

        return redirect($backUrl ?? route("persons.index"))->with('success', 'Персональные данные удалены!');
    }

}
