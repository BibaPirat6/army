<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    public function index()
    {
        $departments = Department::paginate(50);
        return view('admin.org.departments.index', compact('departments'));
    }



    public function show($id)
    {
        $department = Department::findOrFail($id);
        return view('admin.org.departments.show', compact('department'));
    }

    public function create()
    {
        $commissariats = Commissariat::all();
        return view('admin.org.departments.create', compact("commissariats"));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id"
        ], [
            "name.required" => "Название отдела обязательно для заполнения.",
            "name.string" => "Название отдела должно быть строкой.",
            "name.min" => "Название отдела должно содержать минимум 2 символа.",
            "name.max" => "Название отдела не должно превышать 255 символов.",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
        ]);
        Department::create($data);
        return redirect()->route('departments.index')->with('success', 'Отдел успешно создан.');
    }

    public function edit($id)
    {
        $department = Department::with('commissariat')->findOrFail($id);
        $commissariats = Commissariat::all();
        return view('admin.org.departments.edit', compact('department', "commissariats"));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id"
        ], [
            "name.required" => "Название отдела обязательно для заполнения.",
            "name.string" => "Название отдела должно быть строкой.",
            "name.min" => "Название отдела должно содержать минимум 2 символа.",
            "name.max" => "Название отдела не должно превышать 255 символов.",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
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
