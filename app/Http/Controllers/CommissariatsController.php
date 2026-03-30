<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Person;
use App\Models\Position;
use Illuminate\Http\Request;

class CommissariatsController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::all();

        return view('admin.org.commissariats.index', compact('commissariats'));
    }

    public function show(Request $request, $id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $columns = Person::getTableColumns();

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariats.show', compact('commissariat', 'backUrl', 'columns'));
    }

    public function create(Request $request)
    {
        $employees = Employee::all();

        $backUrl = $request->input('back_url');
        $x = $request->input('x');
        $y = $request->input('y');

        return view('admin.org.commissariats.create', compact('employees', 'backUrl', 'x', 'y'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'required|integer|min:1|exists:employees,id',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
        ], [
            'name.required' => 'Название комиссариата обязательно для заполнения.',
            'name.string' => 'Название комиссариата должно быть строкой.',
            'name.min' => 'Название комиссариата должно содержать минимум 2 символа.',
            'name.max' => 'Название комиссариата не должно превышать 255 символов.',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
        ]);

        // 1) Создаем комиссариат
        $commissariat = Commissariat::create([
            'name' => $data['name'],
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
        ]);

        // 2) Создаем назначение сотрудника (employeePosition)
        EmployeePosition::create([
            'employee_id' => $data['chief_employee_id'],
            'commissariat_id' => $commissariat->id,
            'position_id' => Position::whereHas('chiefType', function ($q) {
                $q->where('name', 'Начальник комиссариата');
            })->first()->id,
        ]);

        $backUrl = $request->get('backUrl', route('commissariats.index'));

        return redirect()->to($backUrl)->with('success', 'Комиссариат успешно создан.');
    }

    public function edit(Request $request, $id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $employees = Employee::all();

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariats.edit', compact('commissariat', 'employees', 'backUrl'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'nullable|integer|min:1|exists:employees,id',
            'longitude' => 'nullable|sometimes|integer',
            'latitude' => 'nullable|sometimes|integer',
        ], [
            'name.required' => 'Название комиссариата обязательно для заполнения.',
            'name.string' => 'Название комиссариата должно быть строкой.',
            'name.min' => 'Название комиссариата должно содержать минимум 2 символа.',
            'name.max' => 'Название комиссариата не должно превышать 255 символов.',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
        ]);

        $commissariat = Commissariat::findOrFail($id);

        $commissariat->update([
            'name' => $data['name'],
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
        ]);

        $currentPosition = EmployeePosition::where('commissariat_id', $commissariat->id)->first();

        if ($currentPosition->employee_id != $data['chief_employee_id']) {
            $currentPosition->delete();

            EmployeePosition::create([
                'employee_id' => $data['chief_employee_id'],
                'commissariat_id' => $commissariat->id,
                'position_id' => Position::whereHas('chiefType', function ($q) {
                    $q->where('name', 'Начальник комиссариата');
                })->first()->id,
            ]);
        }

        $backUrl = $request->get('backUrl', route('commissariats.index'));

        return redirect()->to($backUrl)
            ->with('success', 'Комиссариат успешно обновлен.');

    }

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
