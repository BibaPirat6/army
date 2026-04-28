<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;


class CalendarController extends Controller
{
        public function index()
    {
        $commissariats = Commissariat::all();
        return view('admin.calendar.index', compact('commissariats'));
    }
}
