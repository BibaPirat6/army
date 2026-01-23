<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Position;
use Illuminate\Http\Request;

class EmployeePositionsController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['person', 'positions.position'])
            ->whereHas('person', function ($query) {
                $query->whereNotNull('id');
            })
            ->get();
        return view('admin.org.employee-positions.index')->with('employees', $employees);
    }

    public function create($id)
    {
        $employee = Employee::with(["user.role", "workStatus", "person", "positions.position.positionType"])->findOrFail($id);

        $employeePositionIds = $employee->positions->pluck('position_id')->toArray();
        $positions = Position::whereNotIn('id', $employeePositionIds)->get();
        return view('admin.org.employee-positions.create', compact('employee', 'positions'));
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'rate' => 'required|numeric|min:0.25|max:2.0',
        ], [
            'position_id.required' => 'Поле должность обязательно для заполнения.',
            'position_id.integer' => 'Поле должность должно быть целым числом.',
            'position_id.exists' => 'Выбранная должность не существует.',
            'rate.required' => 'Поле ставка обязательно для заполнения.',
            'rate.numeric' => 'Поле ставка должно быть числом.',
            'rate.min' => 'Минимальное значение ставки 0.25.',
            'rate.max' => 'Максимальное значение ставки 2.0.',
        ]);

        Employee::findOrFail($id);

        EmployeePosition::create([
            "employee_id" => $id,
            "position_id" => $data['position_id'],
            "rate" => $data['rate']
        ]);

        return redirect()->route('employee-positions.index')->with('success', 'Должность успешно добавлена сотруднику.');
    }

    public function edit($id)
    {
        $employee = Employee::with(["user.role", "workStatus", "person", "positions.position.positionType"])->findOrFail($id);

        $positions = Position::all();
        return view('admin.org.employee-positions.edit', compact('employee', 'positions'));
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'rate' => 'required|numeric|min:0.25|max:2.0',
        ], [
            'position_id.required' => 'Поле должность обязательно для заполнения.',
            'position_id.integer' => 'Поле должность должно быть целым числом.',
            'position_id.exists' => 'Выбранная должность не существует.',
            'rate.required' => 'Поле ставка обязательно для заполнения.',
            'rate.numeric' => 'Поле ставка должно быть числом.',
            'rate.min' => 'Минимальное значение ставки 0.25.',
            'rate.max' => 'Максимальное значение ставки 2.0.',
        ]);

        $employeePosition = EmployeePosition::findOrFail($id);

        $employeePosition->update([
            "position_id" => $data['position_id'],
            "rate" => $data['rate']
        ]);

        return redirect()->route('employee-positions.index')->with('success', 'Должность успешно обновлена.');
    }

    public function delete($id)
    {
        $employeePosition = EmployeePosition::findOrFail($id);
        $employeePosition->delete();

        return redirect()->route('employee-positions.index')->with('success', 'Должность успешно удалена у сотрудника.');
    }


    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        EmployeePosition::where('employee_id', $employee->id)->delete();

        return redirect()->route('employee-positions.index')->with('success', 'Все назначения должностей успешно удалены у сотрудника.');
    }
}
