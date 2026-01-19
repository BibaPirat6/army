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

        $users = User::withoutTrashed()->get();
        $persons = Person::withoutTrashed()->get();



        return view("admin.employees.index")->with([
            "employees" => $employees,
            "users" => $users,
            "persons" => $persons
        ]);
    }
}

