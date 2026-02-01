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

    public function create()
    {
        $employees = Employee::all();
        return view('admin.org.commissariats.create', compact("employees"));
    }
    public function show($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        return view('admin.org.commissariats.show', compact('commissariat'));
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

        if ($data["chief_employee_id"] !== null) {
            EmployeePosition::updateOrCreate([
                "employee_id" => $data["chief_employee_id"],
                "position_id" => Position::where('name', 'Начальник комиссариата')->value('id'),
                "commissariat_id" => $commissariat->id,
                "rate" => 1,
                "is_chief" => 1,
            ]);
        }

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно создан.');
    }   

    public function edit($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        return view('admin.org.commissariats.edit', compact('commissariat'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "name" => "required|string|min:2|max:255",
        ], [
            "name.required" => "Название комиссариата обязательно для заполнения.",
            "name.string" => "Название комиссариата должно быть строкой.",
            "name.min" => "Название комиссариата должно содержать минимум 2 символа.",
            "name.max" => "Название комиссариата не должно превышать 255 символов.",
        ]);

        Commissariat::where("id", $id)->update($data);

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно обновлен.');
    }

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();
        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
