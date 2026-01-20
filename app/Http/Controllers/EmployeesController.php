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
            "role" => "required|string|in:admin,user",
            "work_status" => "required|string|in:vacant,fired,active",
        ], [
            'role.in' => 'Роль может быть только: admin или user',
            'work_status.in' => 'Статус работы может быть только: vacant, fired или active',
        ]);

        if ($request->has('user_id') && $request->user_id !== 'null') {
            $request->validate([
                'user_id' => 'integer|min:1|exists:users,id'
            ], [
                'user_id.min' => 'ID пользователя должен быть положительным числом',
                'user_id.exists' => 'Выбранный пользователь не существует',
            ]);
            $data['user_id'] = $request->user_id;
        } else {
            $data['user_id'] = null;
        }

        if ($request->has('person_id') && $request->person_id !== 'null') {
            $request->validate([
                'person_id' => 'integer|min:1|exists:persons,id'
            ], [
                'person_id.min' => 'ID персональных данных должен быть положительным числом',
                'person_id.exists' => 'Выбранная персона не существует',
            ]);
            $data['person_id'] = $request->person_id;
        } else {
            $data['person_id'] = null;
        }

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

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->withoutTrashed()
            ->get();

        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->withoutTrashed()
            ->get();

        return view('admin.employees.update')->with([
            "employee" => $employee,
            "users" => $users,
            "persons" => $persons
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            "user_id" => "nullable|integer|min:1|exists:users,id",
            "person_id" => "nullable|integer|min:1|exists:persons,id",
            "role" => "required|string|in:admin,user",
            "work_status" => "required|string|in:vacant,fired,active",
        ], [
            'user_id.min' => 'ID пользователя должен быть положительным числом',
            'person_id.min' => 'ID персональных данных должен быть положительным числом',
            'user_id.exists' => 'Выбранный пользователь не существует',
            'person_id.exists' => 'Выбранная персона не существует',
            'role.in' => 'Роль может быть только: admin или user',
            'work_status.in' => 'Статус работы может быть только: vacant, fired или active',
        ]);

        $user_id = $request->input('user_id', null);
        $person_id = $request->input('person_id', null);

        $updateData = [
            'user_id' => $user_id ?: null,
            'person_id' => $person_id ?: null,
            'role' => $data['role'],
            'work_status' => $data['work_status'],
        ];

        dd($updateData);

        // Employee::findOrFail($id)->update($updateData);

        // return redirect()->route("employees.index")->with("success", "Сотрудник изменен!");
    }

}

