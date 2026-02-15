<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;

class StructureController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::whereNotNull('longitude')
            ->whereNotNull('latitude')
            ->get();
        return view('admin.org.structure.index', compact('commissariats'));
    }

    public function show($id)
    {
        $commissariat = Commissariat::findOrFail($id);

        return view('admin.org.structure.show', compact('commissariat'));
    }
}
