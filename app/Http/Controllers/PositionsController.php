<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\PositionType;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    public function index()
    {
        $positions = Position::with('positionType')->paginate(50);

        return view('admin.org.positions.index')->with('positions', $positions);
    }
    public function show($id)
    {
        $position = Position::findOrFail($id);
        return view('admin.org.positions.show')->with('position', $position);
    }
    public function create()
    {
        $types = PositionType::all();
        return view('admin.org.positions.create')->with('types', $types);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'position_type_id' => 'required|exists:position_types,id',
        ], [
            'name.required' => 'Название должности обязательно для заполнения.',
            'name.string' => 'Название должности должно быть строкой.',
            'name.min' => 'Название должности должно содержать минимум 2 символа.',
            'name.max' => 'Название должности не должно превышать 255 символов.',
            'position_type_id.required' => 'Тип должности обязателен для заполнения.',
            'position_type_id.exists' => 'Выбранный тип должности не существует.',
        ]);

        Position::create($data);

        return redirect()->route('positions.index')->with('success', 'Должность успешно создана.');
    }
    public function edit($id)
    {
        $position = Position::with('positionType')->findOrFail($id);
        $types = PositionType::all();
        return view('admin.org.positions.edit')->with('position', $position)->with('types', $types);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'position_type_id' => 'required|exists:position_types,id',
        ], [
            'name.required' => 'Название должности обязательно для заполнения.',
            'name.string' => 'Название должности должно быть строкой.',
            'name.min' => 'Название должности должно содержать минимум 2 символа.',
            'name.max' => 'Название должности не должно превышать 255 символов.',
            'position_type_id.required' => 'Тип должности обязателен для заполнения.',
            'position_type_id.exists' => 'Выбранный тип должности не существует.',
        ]);

        Position::where("id", $id)->update($data);

        return redirect()->route('positions.index')->with('success', 'Должность успешно обновлена.');
    }
    public function delete($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();
        return redirect()->route('positions.index')->with('success', 'Должность успешно удалена.');
    }
}