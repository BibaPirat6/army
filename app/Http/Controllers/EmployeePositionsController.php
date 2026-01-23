<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePosition;
use Illuminate\Http\Request;

class EmployeePositionsController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['person', 'positions.position'])
            ->whereHas('person', function ($query) {
                $query->whereNotNull('id');
            })
            ->get();
        return view('admin.org.employee-positions.index')->with('employees', $employees);
    }

    public function create($id)
    {
        $employee = Employee::with(["user.role", "workStatus", "person", "positions.position.positionType"])->findOrFail($id);
        return view('admin.org.employee-positions.create', compact('employee'));
    }

    public function store(Request $request)
    {

    }


}
