<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeePositionsController extends Controller
{
    public function create(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $commissariats = Commissariat::all();

        $backUrl = $request->get('back_url');

        // Передаем все переменные явно, а не через compact
        return view('admin.org.employee-positions.create', [
            'employeeId' => $employee->id,
            'backUrl' => $backUrl,
            'commissariats' => $commissariats,
        ]);
    }
}
