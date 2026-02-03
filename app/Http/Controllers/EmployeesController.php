<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkStatus;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user', 'person'])
            ->paginate(9);

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->get();

        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->get();

        $roles = Role::all();
        $statuses = WorkStatus::all();

        return view("admin.employees.index")->with([
            "employees" => $employees,
            "users" => $users,
            "persons" => $persons,
            "roles" => $roles,
            "statuses" => $statuses,
        ]);
    }

    public function create()
    {
        $employees = Employee::with(['user', 'person'])
            ->get();

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->get();

        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->get();

        $roles = Role::all();
        $statuses = WorkStatus::all();

        return view("admin.employees.create")->with([
            "employees" => $employees,
            "users" => $users,
            "persons" => $persons,
            "roles" => $roles,
            "statuses" => $statuses,
        ]);
    }

    public function store(Request $request)
    {
        $requestData = $request->all();
        if (isset($requestData['user_id']) && $requestData['user_id'] === '') {
            $requestData['user_id'] = null;
        }
        if (isset($requestData['person_id']) && $requestData['person_id'] === '') {
            $requestData['person_id'] = null;
        }

        $data = validator($requestData, [
            "work_status" => "required|integer|exists:work_statuses,id",
            "user_id" => "nullable|integer|min:1|exists:users,id",
            "person_id" => "nullable|integer|min:1|exists:persons,id",
        ], [
            'work_status.required' => 'Рабочий статус обязателен',
            'work_status.exists' => 'Выбранный статус работы не существует',
            'user_id.min' => 'ID пользователя должен быть положительным числом',
            'user_id.exists' => 'Выбранный пользователь не существует',
            'person_id.min' => 'ID персональных данных должен быть положительным числом',
            'person_id.exists' => 'Выбранная персона не существует',
        ])->validate();

        $employeeData = [
            'work_status_id' => $data['work_status'],
            'user_id' => $data['user_id'] ?? null,
            'person_id' => $data['person_id'] ?? null,
        ];

        Employee::create($employeeData);

        return redirect()->route("employees.index")->with("success", "Сотрудник создан!");
    }



    public function edit($id)
    {
        $employee = Employee::findOrFail($id);

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->get();

        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->get();

        $roles = Role::all();
        $statuses = WorkStatus::all();

        return view('admin.employees.edit')->with([
            "employee" => $employee,
            "users" => $users,
            "persons" => $persons,
            "roles" => $roles,
            "statuses" => $statuses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $requestData = $request->all();
        if (isset($requestData['user_id']) && $requestData['user_id'] === '') {
            $requestData['user_id'] = null;
        }
        if (isset($requestData['person_id']) && $requestData['person_id'] === '') {
            $requestData['person_id'] = null;
        }

        $data = validator($requestData, [
            "work_status" => "required|integer|exists:work_statuses,id",
            "user_id" => "nullable|integer|min:1|exists:users,id",
            "person_id" => "nullable|integer|min:1|exists:persons,id",
        ], [
            'work_status.required' => 'Рабочий статус обязателен',
            'work_status.exists' => 'Выбранный статус работы не существует',
            'user_id.min' => 'ID пользователя должен быть положительным числом',
            'user_id.exists' => 'Выбранный пользователь не существует',
            'person_id.min' => 'ID персональных данных должен быть положительным числом',
            'person_id.exists' => 'Выбранная персона не существует',
        ])->validate();

        $employeeData = [
            'work_status_id' => $data['work_status'] ?? $employee->work_status_id,
            'user_id' => $data['user_id'] !== null ? $data['user_id'] : $employee->user_id,
            'person_id' => $data['person_id'] !== null ? $data['person_id'] : $employee->person_id,
        ];

        $employee->update($employeeData);

        return redirect()->route("employees.index")->with("success", "Сотрудник обновлен!");
    }

    public function delete($id)
    {
        EmployeePosition::where('employee_id', $id)->delete();
        $res = Employee::where('id', $id)->delete();

        if ($res) {
            return redirect()->route('employees.index')
                ->with('success', 'Сотрудник удален');
        }

        return redirect()->back()
            ->with('error', 'Не удалось удалить сотрудника');
    }
}

