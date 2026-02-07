<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Position;
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

        $employees = Employee::all();

        return view('admin.org.divisions.create', compact("commissariats", "departments", "employees"));
    }

    public function store(Request $request)
    {
        dd($request->all());
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "department_id" => "nullable|sometimes|exists:departments,id",
            "chief_employee_id" => "nullable|sometimes|integer|min:1|exists:employees,id"
        ], [
            "name.required" => "Название подразделения обязательно для заполнения.",
            "name.string" => "Название подразделения должно быть строкой.",
            "name.min" => "Название подразделения должно содержать минимум 2 символа.",
            "name.max" => "Название подразделения не должно превышать 255 символов.",
            "is_active.boolean" => "Некорректное значение для поля активности",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
            "department_id.exists" => "Несуществующий отдел",
            "chief_employee_id.exists" => "Несуществующий сотрудник",
        ]);

        $division = Division::create($data);
        $division->refresh();

        if ($data["chief_employee_id"] !== null) {
            $positionId = Position::where('name', 'Начальник отделения')->value('id');

            EmployeePosition::updateOrCreate(
                [
                    "employee_id" => $data["chief_employee_id"],
                    "position_id" => $positionId,
                    "commissariat_id" => $data["commissariat_id"],
                    "department_id" => $data["department_id"],
                    "division_id" => $division->id
                ],
                [
                    "rate" => 1,
                ]
            );
        }

        return redirect()->route('divisions.index')->with('success', 'Отделение успешно создано.');
    }

    public function edit($id)
    {
        $division = Division::findOrFail($id);
        $commissariats = Commissariat::all();
        $departments = Department::all();
        $employees = Employee::all();

        return view('admin.org.divisions.edit', compact('division', 'commissariats', 'departments', "employees"));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "department_id" => "nullable|sometimes|exists:departments,id",
            "chief_employee_id" => "nullable|sometimes|integer|min:1|exists:employees,id",
        ], [
            "name.required" => "Название подразделения обязательно для заполнения.",
            "name.string" => "Название подразделения должно быть строкой.",
            "name.min" => "Название подразделения должно содержать минимум 2 символа.",
            "name.max" => "Название подразделения не должно превышать 255 символов.",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
            "department_id.exists" => "Несуществующий отдел",
            "chief_employee_id.exists" => "Несуществующий сотрудник",
        ]);

        $division = Division::findOrFail($id);
        Commissariat::findOrFail($data['commissariat_id']); // Проверка существования комиссариата

        // Сохраняем старые значения для корректного удаления предыдущей должности
        $oldChiefEmployeeId = $division->chief_employee_id;
        $oldCommissariatId = $division->commissariat_id;
        $oldDepartmentId = $division->department_id;

        // Обновляем подразделение
        $division->update([
            'name' => $data['name'],
            'commissariat_id' => $data['commissariat_id'],
            'department_id' => $data['department_id'], // Может быть null
            'chief_employee_id' => $data['chief_employee_id'],
        ]);

        // Получаем ID должности "Начальник отделения"
        $chiefPositionId = Position::where('name', 'Начальник отделения')->value('id');
        if (!$chiefPositionId) {
            return back()->withErrors(['error' => 'Должность "Начальник отделения" не найдена в системе.']);
        }

        // Удаляем предыдущую запись начальника (в старом контексте: старый комиссариат/отдел)
        if ($oldChiefEmployeeId !== null) {
            EmployeePosition::where([
                'employee_id' => $oldChiefEmployeeId,
                'position_id' => $chiefPositionId,
                'commissariat_id' => $oldCommissariatId,
                'department_id' => $oldDepartmentId, // null обрабатывается корректно
                'division_id' => $division->id,
            ])->delete();
        }

        // Создаём/обновляем запись для нового начальника (в новом контексте)
        if ($data['chief_employee_id'] !== null) {
            EmployeePosition::updateOrCreate(
                [
                    'employee_id' => $data['chief_employee_id'],
                    'position_id' => $chiefPositionId,
                    'commissariat_id' => $data['commissariat_id'],
                    'department_id' => $data['department_id'], // null допустим
                    'division_id' => $division->id,
                ],
                [
                    'rate' => 1,
                ]
            );
        }

        return redirect()->route('divisions.index')->with('success', 'Отделение успешно обновлено.');
    }

    public function delete($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();
        return redirect()->route('divisions.index')->with('success', 'Подразделение успешно удалено.');
    }
}
