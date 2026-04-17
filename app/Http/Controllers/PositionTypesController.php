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

    public function show(Request $request, $id)
    {
        $type = PositionType::findOrFail($id);
        $backUrl = $request->input('back_url');

        return view('admin.org.position-types.show', compact('type', 'backUrl'));
    }

    public function create(Request $request)
    {
        $backUrl = $request->input('back_url');

        return view('admin.org.position-types.create', compact('backUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255|unique:position_types,name',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.min' => 'Минимальная длина названия - 2 символа.',
            'name.max' => 'Максимальная длина названия - 255 символов.',
            'name.unique' => 'Тип должности с таким названием уже существует.',
        ]);

        PositionType::create($data);
        $backUrl = $request->get('backUrl', route('position-types.index'));

        return redirect()->to($backUrl)->with('success', 'Тип должности успешно создан.');
    }

    public function edit(Request $request, $id)
    {
       
        $type = PositionType::findOrFail($id);
        $backUrl = $request->input('back_url');

        return view('admin.org.position-types.edit', compact('backUrl', 'type'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255|unique:position_types,name',
        ], [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.min' => 'Минимальная длина названия - 2 символа.',
            'name.max' => 'Максимальная длина названия - 255 символов.',
            'name.unique' => 'Тип должности с таким названием уже существует.',
        ]);

        PositionType::where('id', $id)->update($data);


           $backUrl = $request->get('backUrl', route('position-types.index'));

        return redirect()->to($backUrl)->with('success', 'Тип должности успешно обновлен.');
    }

    public function delete(Request $request,$id)
    {
        $type = PositionType::findOrFail($id);
        $type->delete();
        $backUrl = $request->get('backUrl', route('position-types.index'));
        return redirect()->to($backUrl)->with('success', 'Тип должности успешно удален.');
    }
}
