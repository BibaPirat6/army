<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Position;
use Illuminate\Http\Request;

class CommissariatsController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::paginate(50);
        return view('admin.org.commissariats.index', compact('commissariats'));
    }
    public function show($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        return view('admin.org.commissariats.show', compact('commissariat'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('admin.org.commissariats.create', compact("employees"));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "chief_employee_id" => "nullable|sometimes|integer|min:1|exists:employees,id"
        ], [
            "name.required" => "Название комиссариата обязательно для заполнения.",
            "name.string" => "Название комиссариата должно быть строкой.",
            "name.min" => "Название комиссариата должно содержать минимум 2 символа.",
            "name.max" => "Название комиссариата не должно превышать 255 символов.",
            "chief_employee_id.exists" => "Несуществующий сотрудник"
        ]);

        $commissariat = Commissariat::create($data);
        $commissariat->refresh();

        $positionId = Position::where('name', 'Начальник комиссариата')->value('id');

        if ($data["chief_employee_id"] !== null) {
            EmployeePosition::updateOrCreate(
                [
                    "employee_id" => $data["chief_employee_id"],
                    "position_id" => $positionId,
                    "commissariat_id" => $commissariat->id,
                ],
                [
                    "rate" => 1,
                    "is_chief" => 1,
                ]
            );
        }

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно создан.');
    }

    public function edit($id)
    {
        $commissariat = Commissariat::with('chiefEmployee.person')->findOrFail($id);

        $employees = Employee::all();
        return view('admin.org.commissariats.edit', compact('commissariat', "employees"));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
            "chief_employee_id" => "nullable|integer|min:1|exists:employees,id"
        ], [
            "name.required" => "Название комиссариата обязательно для заполнения.",
            "name.string" => "Название комиссариата должно быть строкой.",
            "name.min" => "Название комиссариата должно содержать минимум 2 символа.",
            "name.max" => "Название комиссариата не должно превышать 255 символов.",
            "chief_employee_id.exists" => "Несуществующий сотрудник"
        ]);

        $commissariat = Commissariat::findOrFail($id);

        // Сохраняем ID старого начальника перед обновлением
        $oldChiefEmployeeId = $commissariat->chief_employee_id;

        // Обновляем комиссариат
        $commissariat->update([
            "name" => $data["name"],
            "chief_employee_id" => $data["chief_employee_id"] // null или новое значение
        ]);

        // Получаем ID должности "Начальник комиссариата"
        $chiefPositionId = Position::where('name', 'Начальник комиссариата')->value('id');

        if (!$chiefPositionId) {
            return back()->withErrors(['error' => 'Должность "Начальник комиссариата" не найдена']);
        }

        // Если указан новый начальник
        if ($data["chief_employee_id"] !== null) {
            // 1. Удаляем старую запись начальника (если был старый начальник)
            if ($oldChiefEmployeeId !== null) {
                EmployeePosition::where([
                    'employee_id' => $oldChiefEmployeeId,
                    'position_id' => $chiefPositionId,
                    'commissariat_id' => $commissariat->id,
                    'is_chief' => 1
                ])->delete();
            }

            // 2. Создаем новую запись для нового начальника
            EmployeePosition::updateOrCreate([
                "employee_id" => $data["chief_employee_id"],
                "position_id" => $chiefPositionId,
                "commissariat_id" => $commissariat->id,
            ], [
                "rate" => 1,
                "is_chief" => 1,
            ]);

            // 3. Также удаляем другие записи этого сотрудника как начальника в этом комиссариате
            // EmployeePosition::where('employee_id', $data["chief_employee_id"])
            //     ->where('commissariat_id', $commissariat->id)
            //     ->where('position_id', '!=', $chiefPositionId)
            //     ->where('is_chief', 1)
            //     ->delete();

        } else {
            // Если начальник удален (установлен null)
            // Удаляем запись из EmployeePosition для старого начальника
            if ($oldChiefEmployeeId !== null) {
                EmployeePosition::where([
                    'employee_id' => $oldChiefEmployeeId,
                    'position_id' => $chiefPositionId,
                    'commissariat_id' => $commissariat->id,
                    'is_chief' => 1
                ])->delete();
            }
        }

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно обновлен.');
    }

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();
        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
