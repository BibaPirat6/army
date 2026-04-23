<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{
    public function index()
    {
        return view('admin.excel-export.index');
    }

    /**
     * Экспорт всех сотрудников
     */
    /**
     * Экспорт сотрудников
     */
    public function employee()
    {
        return Excel::download(new EmployeeExport, 'employees_'.date('Y-m-d_H-i-s').'.xlsx');
    }
}
