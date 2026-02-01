<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionsController extends Controller
{
    public function index()
    {
        $divisions = Division::paginate(50);
        return view('admin.org.divisions.index', compact('divisions'));
    }

    public function show($id)
    {
        $division = Division::findOrFail($id);
        return view('admin.org.divisions.show', compact('division'));
    }

    public function create()
    {
        $commissariats = Commissariat::all();
        $departments = Department::all();
        return view('admin.org.divisions.create', compact("commissariats", "departments"));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "department_id" => "nullable|sometimes|exists:departments,id"
        ], [
            "name.required" => "Название подразделения обязательно для заполнения.",
            "name.string" => "Название подразделения должно быть строкой.",
            "name.min" => "Название подразделения должно содержать минимум 2 символа.",
            "name.max" => "Название подразделения не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
            "department_id.exists" => "Несуществующий отдел"
        ]);
        Division::create($data);
        return redirect()->route('divisions.index')->with('success', 'Подразделение успешно создано.');
    }

    public function edit($id)
    {
        $division = Division::findOrFail($id);
        $commissariats = Commissariat::all();
        $departments = Department::all();

        return view('admin.org.divisions.edit', compact('division', 'commissariats', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "department_id" => "nullable|sometimes|exists:departments,id"
        ], [
            "name.required" => "Название подразделения обязательно для заполнения.",
            "name.string" => "Название подразделения должно быть строкой.",
            "name.min" => "Название подразделения должно содержать минимум 2 символа.",
            "name.max" => "Название подразделения не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
            "department_id.exists" => "Несуществующий отдел"
        ]);

        Division::where("id", $id)->update($data);

        return redirect()->route('divisions.index')->with('success', 'Подразделение успешно обновлено.');
    }

    public function delete($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();
        return redirect()->route('divisions.index')->with('success', 'Подразделение успешно удалено.');
    }
}
