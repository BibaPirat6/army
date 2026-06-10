<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Person;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;
        $columns = Person::getAllColumns();

        return view('profile.index', compact('employee', 'columns'));
    }
}