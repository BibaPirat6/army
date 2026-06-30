<?php

namespace App\Http\Controllers;

use App\Models\Person;

class ProfileController extends Controller
{
    public function index()
    {
        $employee = auth()->user()?->employee;
        $columns = Person::getAllColumns();

        $taskAssignments = collect();

        if ($employee) {
            $taskAssignments = $employee->taskAssignments()
                ->with(['task'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('profile.index', compact('employee', 'columns', 'taskAssignments'));
    }
}