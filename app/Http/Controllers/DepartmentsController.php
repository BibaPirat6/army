<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Position;
use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    public function index()
    {
        $departments = Department::paginate(50);
        return view('admin.org.departments.index', compact('departments'));
    }



    public function show(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        $backUrl = $request->input("back_url");
        return view('admin.org.departments.show', compact('department', 'backUrl'));
    }

    public function create(Request $request)
    {
        $commissariats = Commissariat::all();
        $employees = Employee::all();

        $commissariatId = $request->get('commissariat_id');
        $commissariat = $commissariatId
            ? Commissariat::find($commissariatId)
            : null;

        $backUrl = $request->get("back_url");


        return view('admin.org.departments.create', compact("commissariats", "employees", 'commissariat', 'backUrl'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "chief_employee_id" => "nullable|sometimes|integer|min:1|exists:employees,id"
        ], [
            "name.required" => "Название отдела обязательно для заполнения.",
            "name.string" => "Название отдела должно быть строкой.",
            "name.min" => "Название отдела должно содержать минимум 2 символа.",
            "name.max" => "Название отдела не должно превышать 255 символов.",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
            "chief_employee_id.exists" => "Несуществующий сотрудник",
        ]);

        $department = Department::create($data);
        $department->refresh();

        if ($data["chief_employee_id"] !== null) {
            $positionId = Position::where('name', 'Начальник отдела')->value('id');

            EmployeePosition::updateOrCreate(
                [
                    "employee_id" => $data["chief_employee_id"],
                    "position_id" => $positionId,
                    "commissariat_id" => $data["commissariat_id"],
                    "department_id" => $department->id,
                ],
                [
                    "rate" => 1,
                ]
            );
        }

        $backUrl = $request->get("backUrl", route('departments.index'));
        return redirect()->to($backUrl)->with('success', 'Отдел успешно создан.');
    }

    public function edit(Request $request, $id)
    {
        $department = Department::with('commissariat')->findOrFail($id);
        $commissariats = Commissariat::all();
        $employees = Employee::all();

        $backUrl = $request->input("back_url");
        return view('admin.org.departments.edit', compact('department', "commissariats", "employees", "backUrl"));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "commissariat_id" => "required|integer|min:1|exists:commissariats,id",
            "chief_employee_id" => "nullable|integer|min:1|exists:employees,id"
        ], [
            "name.required" => "Название комиссариата обязательно для заполнения.",
            "name.string" => "Название комиссариата должно быть строкой.",
            "name.min" => "Название комиссариата должно содержать минимум 2 символа.",
            "name.max" => "Название комиссариата не должно превышать 255 символов.",
            "chief_employee_id.exists" => "Несуществующий сотрудник",
            "commissariat_id.required" => "Выберите комиссариат",
            "commissariat_id.exists" => "Несуществующий комиссариат",
        ]);

        $department = Department::findOrFail($id);
        $commissariat = Commissariat::findOrFail($data["commissariat_id"]);

        // Сохраняем ID старого начальника перед обновлением
        $oldChiefEmployeeId = $department->chief_employee_id;

        // Обновляем комиссариат
        $department->update([
            "name" => $data["name"],
            "commissariat_id" => $data["commissariat_id"],
            "chief_employee_id" => $data["chief_employee_id"] // null или новое значение
        ]);

        // Получаем ID должности "Начальник комиссариата"
        $chiefPositionId = Position::where('name', 'Начальник отдела')->value('id');

        if (!$chiefPositionId) {
            return back()->withErrors(['error' => 'Должность "Начальник отдела" не найдена']);
        }

        // Если указан новый начальник
        if ($data["chief_employee_id"] !== null) {
            // 1. Удаляем старую запись начальника (если был старый начальник)
            if ($oldChiefEmployeeId !== null) {
                EmployeePosition::where([
                    'employee_id' => $oldChiefEmployeeId,
                    'position_id' => $chiefPositionId,
                    'commissariat_id' => $commissariat->id,
                    "department_id" => $department->id,
                ])->delete();
            }

            // 2. Создаем новую запись для нового начальника
            EmployeePosition::updateOrCreate([
                "employee_id" => $data["chief_employee_id"],
                "position_id" => $chiefPositionId,
                "commissariat_id" => $commissariat->id,
                "department_id" => $department->id,
            ], [
                "rate" => 1,
            ]);

        } else {
            // Если начальник удален (установлен null)
            // Удаляем запись из EmployeePosition для старого начальника
            if ($oldChiefEmployeeId !== null) {
                EmployeePosition::where([
                    'employee_id' => $oldChiefEmployeeId,
                    'position_id' => $chiefPositionId,
                    'commissariat_id' => $commissariat->id,
                    "department_id" => $department->id,
                ])->delete();
            }
        }


        $backUrl = $request->get("backUrl", route('departments.index'));

        return redirect()->to($backUrl)->with('success', 'Отдел успешно обновлен.');
    }

    public function delete($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Отдел успешно удален.');
    }
}
