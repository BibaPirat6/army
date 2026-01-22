<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionsController extends Controller
{
    public function index()
    {
        $divisions = Division::all();
        return view('admin.org.divisions.index', compact('divisions'));
    }

    public function create()
    {
        return view('admin.org.divisions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "is_active" => "boolean|in:0,1"
        ], [
            "name.required" => "Название подразделения обязательно для заполнения.",
            "name.string" => "Название подразделения должно быть строкой.",
            "name.min" => "Название подразделения должно содержать минимум 2 символа.",
            "name.max" => "Название подразделения не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности"
        ]);
        Division::create($data);
        return redirect()->route('divisions.index')->with('success', 'Подразделение успешно создано.');
    }

    public function edit($id)
    {
        $division = Division::findOrFail($id);
        return view('admin.org.divisions.edit', compact('division'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "is_active" => "boolean|in:0,1"
        ], [
            "name.required" => "Название подразделения обязательно для заполнения.",
            "name.string" => "Название подразделения должно быть строкой.",
            "name.min" => "Название подразделения должно содержать минимум 2 символа.",
            "name.max" => "Название подразделения не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности"
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
