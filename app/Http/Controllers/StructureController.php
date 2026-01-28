<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use Illuminate\Http\Request;

class StructureController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::all();
        return view('admin.org.structure.index', compact('commissariats'));
    }

    public function show($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        

        return view('admin.org.structure.show', compact('commissariat'));
    }
}
