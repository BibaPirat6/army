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
        dd($request->all());
    }
}

