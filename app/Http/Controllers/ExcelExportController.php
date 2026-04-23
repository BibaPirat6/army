<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeExport;
use App\Exports\StructureExport;
use App\Models\Commissariat;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::all();

        return view('admin.excel-export.index', compact('commissariats'));
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

    public function structure($id)
    {
        // Находим комиссариат по ID
        $commissariat = Commissariat::findOrFail($id);

        // Передаем комиссариат в конструктор экспорта
        return Excel::download(new StructureExport($commissariat), 'structure_'.$commissariat->name.'_'.date('Y-m-d_H-i-s').'.xlsx');
    }
}
