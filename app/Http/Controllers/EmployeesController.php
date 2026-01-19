<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user', 'person'])
            ->withoutTrashed()
            ->get();

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->withoutTrashed()
            ->get();

        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->withoutTrashed()
            ->get();

        return view("admin.employees.index")->with([
            "employees" => $employees,
            "users" => $users,
            "persons" => $persons
        ]);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            "user_id" => "integer|exists:users,id",
            "person_id" => "integer|exists:persons,id",
            "role" => "required|string|in:admin,user",
            "work_status" => "required|string|in:vacant,fired,active",
        ], [
            'user_id.exists' => 'Выбранный пользователь не существует',
            'person_id.exists' => 'Выбранная персона не существует',
            'role.in' => 'Роль может быть только: admin или user',
            'work_status.in' => 'Статус работы может быть только: vacant, fired или active',
        ]);

        Employee::create($data);

        return redirect()->route("employees.index")->with("success", "Сотрудник создан!");
    }

    public function delete($id)
    {
        $res = Employee::where('id', $id)->delete();

        if ($res) {
            return redirect()->route('employees.index')
                ->with('success', 'Сотрудник удален');
        }

        return redirect()->back()
            ->with('error', 'Не удалось удалить сотрудника');
    }

    public function updateShow($id)
    {
        $employee = Employee::findOrFail($id);

        return view('admin.employees.update')->with("employee", $employee);
    }
}

