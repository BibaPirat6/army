<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return view('admin.org.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.org.departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "is_active" => "boolean|in:0,1"
        ], [
            "name.required" => "Название отдела обязательно для заполнения.",
            "name.string" => "Название отдела должно быть строкой.",
            "name.min" => "Название отдела должно содержать минимум 2 символа.",
            "name.max" => "Название отдела не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности"
        ]);
        Department::create($data);
        return redirect()->route('departments.index')->with('success', 'Отдел успешно создан.');
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return view('admin.org.departments.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "is_active" => "boolean|in:0,1"
        ], [
            "name.required" => "Название отдела обязательно для заполнения.",
            "name.string" => "Название отдела должно быть строкой.",
            "name.min" => "Название отдела должно содержать минимум 2 символа.",
            "name.max" => "Название отдела не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности"
        ]);

        Department::where("id", $id)->update($data);

        return redirect()->route('departments.index')->with('success', 'Отдел успешно обновлен.');
    }

    public function delete($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Отдел успешно удален.');
    }
}
