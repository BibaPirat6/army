<?php

namespace App\Http\Controllers;

use App\Models\ChiefType;
use App\Models\Commissariat;
use App\Models\Position;
use App\Models\PositionType;
use Illuminate\Http\Request;

class PositionsController extends Controller
{
    public function index(Request $request)
    {
        // фильтр
        $positions = Position::orderBy('id', 'desc')->paginate(50);

        $commissariats = Commissariat::all();

        return view('admin.org.positions.index', compact('positions', 'commissariats'));
    }

    public function show(Request $request, $id)
    {
        $position = Position::findOrFail($id);
        $backUrl = $request->input('back_url');

        return view('admin.org.positions.show', compact('position', 'backUrl'));
    }

    public function create(Request $request)
    {
        $types = PositionType::all();
        $chiefTypes = ChiefType::all();
        $backUrl = $request->input('back_url');

        return view('admin.org.positions.create', compact('types', 'chiefTypes', 'backUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'position_type_id' => 'nullable|sometimes|exists:position_types,id',
            'chief_type_id' => 'required|exists:chief_types,id',
        ], [
            'name.required' => 'Название должности обязательно для заполнения.',
            'name.string' => 'Название должности должно быть строкой.',
            'name.min' => 'Название должности должно содержать минимум 2 символа.',
            'name.max' => 'Название должности не должно превышать 255 символов.',
            'position_type_id.exists' => 'Выбранный тип должности не существует.',
            'chief_type_id.required' => 'Надо выбрать тип начальника',
        ]);

        Position::create($data);

        $backUrl = $request->get('backUrl', route('positions.index'));

        return redirect()->to($backUrl)->with('success', 'Должность успешно создана.');
    }

    public function edit(Request $request, $id)
    {
        $position = Position::with('positionType')->findOrFail($id);
        $types = PositionType::all();
        $chiefTypes = ChiefType::all();
        $backUrl = $request->input('back_url');

        return view('admin.org.positions.edit', compact('position', 'types', 'chiefTypes', 'backUrl'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'position_type_id' => 'nullable|sometimes|exists:position_types,id',
            'chief_type_id' => 'required|exists:chief_types,id',
        ], [
            'name.required' => 'Название должности обязательно для заполнения.',
            'name.string' => 'Название должности должно быть строкой.',
            'name.min' => 'Название должности должно содержать минимум 2 символа.',
            'name.max' => 'Название должности не должно превышать 255 символов.',
            'position_type_id.exists' => 'Выбранный тип должности не существует.',
            'chief_type_id.required' => 'Надо выбрать тип начальника',
        ]);

        Position::where('id', $id)->update($data);

        $backUrl = $request->get('backUrl', route('positions.index'));

        return redirect()->to($backUrl)->with('success', 'Должность успешно обновлена.');
    }

    public function delete(Request $request, $id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        $backUrl = $request->get('backUrl', route('positions.index'));

        return redirect()->to($backUrl)->with('success', 'Должность успешно удалена.');
    }
}
