<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use Illuminate\Http\Request;

class CommissariatsController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::all();
        return view('admin.org.commissariats.index', compact('commissariats'));
    }

    public function create()
    {
        return view('admin.org.commissariats.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "is_active" => "boolean|in:0,1"
        ], [
            "name.required" => "Название комиссариата обязательно для заполнения.",
            "name.string" => "Название комиссариата должно быть строкой.",
            "name.min" => "Название комиссариата должно содержать минимум 2 символа.",
            "name.max" => "Название комиссариата не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности"
        ]);
        Commissariat::create($data);
        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно создан.');
    }

    public function edit($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        return view('admin.org.commissariats.edit', compact('commissariat'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "is_active" => "boolean|in:0,1"
        ], [
            "name.required" => "Название комиссариата обязательно для заполнения.",
            "name.string" => "Название комиссариата должно быть строкой.",
            "name.min" => "Название комиссариата должно содержать минимум 2 символа.",
            "name.max" => "Название комиссариата не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности"
        ]);

        Commissariat::where("id", $id)->update($data);

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно обновлен.');
    }

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();
        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
