<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
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
            ->paginate(20);
        return view('admin.org.employee-positions.index')->with('employees', $employees);
    }
    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view("admin.org.employee-positions.show", compact("employee"));
    }

    public function create(Request $request, $id)
    {
        $employee = Employee::with(["user.role", "workStatus", "person", "positions.position.positionType"])->findOrFail($id);
        $positions = Position::all();
        $commissariats = Commissariat::all();
        $departments = Department::all();
        $divisions = Division::all();

        $backUrl = $request->get("back_url");
        $employeeId = $id;

        return view('admin.org.employee-positions.create', compact('employee', 'positions', "commissariats", "departments", "divisions", "backUrl", "employeeId"));
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'rate' => 'required|numeric|min:0.25|max:2.0',
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "department_id" => "nullable|sometimes|exists:departments,id",
            "division_id" => "nullable|sometimes|exists:divisions,id",
            "is_independent" => "required|integer|in:1,0",
            "employeeId" => "nullable|integer|min:1|exists:employees,id"
        ], [
            'position_id.required' => 'Поле должность обязательно для заполнения.',
            'position_id.integer' => 'Поле должность должно быть целым числом.',
            'position_id.exists' => 'Выбранная должность не существует.',
            'rate.required' => 'Поле ставка обязательно для заполнения.',
            'rate.numeric' => 'Поле ставка должно быть числом.',
            'rate.min' => 'Минимальное значение ставки 0.25.',
            'rate.max' => 'Максимальное значение ставки 2.0.',
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
            "department_id.exists" => "Несуществующий отдел",
            "division_id.exists" => "Несуществующий отдел",
            "is_independent.required" => "Выберите тип должность самостоятельная/нет",
            "employeeId.exists" => "Не существующий id сотрудника",
        ]);

        Employee::findOrFail($id);

        EmployeePosition::create([
            "employee_id" => $id,
            "commissariat_id" => $data["commissariat_id"],
            "department_id" => $data["department_id"],
            "division_id" => $data["division_id"],
            "position_id" => $data['position_id'],
            "rate" => $data['rate'],
            "is_independent" => $data["is_independent"]
        ]);

        $backUrl = $request->input("backUrl");

        return redirect($backUrl ?? route("employee-positions.index"))->with('success', 'Должность успешно добавлена сотруднику.');
    }

    public function edit(Request $request, $id)
    {
        $employee = Employee::with(["user.role", "workStatus", "person", "positions.position.positionType"])->findOrFail($id);
        $positions = Position::all();
        $commissariats = Commissariat::all();
        $departments = Department::all();
        $divisions = Division::all();

        $backUrl = $request->get("back_url");
        $employeeId = $id;

        return view('admin.org.employee-positions.edit', compact('employee', 'positions', "commissariats", "departments", "divisions", "backUrl", "employeeId"));
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'rate' => 'required|numeric|min:0.25|max:2.0',
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "department_id" => "nullable|sometimes|integer|min:1|exists:departments,id",
            "division_id" => "nullable|sometimes|integer|min:1|exists:divisions,id",
            "is_independent" => "required|in:0,1",
        ], [
            'position_id.required' => 'Поле должность обязательно для заполнения.',
            'position_id.integer' => 'Поле должность должно быть целым числом.',
            'position_id.exists' => 'Выбранная должность не существует.',
            'rate.required' => 'Поле ставка обязательно для заполнения.',
            'rate.numeric' => 'Поле ставка должно быть числом.',
            'rate.min' => 'Минимальное значение ставки 0.25.',
            'rate.max' => 'Максимальное значение ставки 2.0.',
            "commissariat_id.required" => "Обязательное поле комиссариата",
            "commissariat_id.exists" => "Несуществующий комиссариат",
            "department_id.required" => "Обязательное поле отдела",
            "department_id.exists" => "Несуществующий отдел",
            "division_id.required" => "Обязательное поле отделения",
            "division_id.exists" => "Несуществующее отделение",
            "is_independent.required" => "Обязательное поле выбора самостоятельной должности"
        ]);

        $employeePosition = EmployeePosition::findOrFail($id);

        $employeePosition->update([
            "position_id" => $data['position_id'],
            "rate" => $data['rate'],
            "commissariat_id" => $data['commissariat_id'],
            "department_id" => $data['department_id'],
            "division_id" => $data['division_id'],
            "is_independent" => $data['is_independent'],
        ]);

        $backUrl = $request->input("backUrl");

        return redirect($backUrl ?? route('employee-positions.index'))->with('success', 'Должность успешно обновлена.');
    }

    public function delete(Request $request, $id)
    {
        $employeePosition = EmployeePosition::findOrFail($id);
        $employeePosition->delete();

        $backUrl = $request->get("back_url");

        return redirect($backUrl ?? route('employee-positions.index'))->with('success', 'Должность успешно удалена у сотрудника.');
    }


    public function destroy(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        EmployeePosition::where('employee_id', $employee->id)->delete();

        $backUrl = $request->get("back_url");

        return redirect($backUrl ?? route('employee-positions.index'))->with('success', 'Все назначения должностей успешно удалены у сотрудника.');
    }
}
