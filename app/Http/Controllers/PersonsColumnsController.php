<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonsColumnsController extends Controller
{
    public function index(Request $request)
    {
        $backUrl = $request->input('back_url');

        $columns = Person::getTableColumns();




        return view("admin.persons.persons-columns.index", compact("backUrl", "columns"));
    }

    public function create(Request $request)
    {
        $backUrl = $request->input("back_url");
        return view("admin.persons.persons-columns.create", compact("backUrl"));
    }
}
