<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;


use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ProfileController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;

        return view('profile.index')->with('employee', $employee);
    }
}
