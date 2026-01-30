<?php

namespace App\Http\Controllers;

use App\Models\PositionType;
use Illuminate\Http\Request;

class PositionTypesController extends Controller
{
    public function index()
    {
        $types = PositionType::paginate(50);
        return view('admin.org.position-types.index')->with('types', $types);
    }
    public function show($id)
    {
        $type = PositionType::findOrFail($id);
        return view('admin.org.position-types.show')->with("type", $type);
    }

    public function create()
    {
        return view('admin.org.position-types.create');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255|unique:position_types,name',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            "name.min" => 'Минимальная длина названия - 2 символа.',
            "name.max" => 'Максимальная длина названия - 255 символов.',
            'name.unique' => 'Тип должности с таким названием уже существует.',
        ]);

        PositionType::create($data);

        return redirect()->route('position-types.index')->with('success', 'Тип должности успешно создан.');
    }
    public function edit($id)
    {
        $type = PositionType::findOrFail($id);
        return view('admin.org.position-types.edit')->with('type', $type);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255|unique:position_types,name',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            "name.min" => 'Минимальная длина названия - 2 символа.',
            "name.max" => 'Максимальная длина названия - 255 символов.',
            'name.unique' => 'Тип должности с таким названием уже существует.',
        ]);

        PositionType::where('id', $id)->update($data);

        return redirect()->route('position-types.index')->with('success', 'Тип должности успешно обновлен.');
    }

    public function delete($id)
    {
        $type = PositionType::findOrFail($id);
        $type->delete();

        return redirect()->route('position-types.index')->with('success', 'Тип должности успешно удален.');
    }
}
