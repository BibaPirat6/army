<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeExport;
use App\Exports\StructureExport;
use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;
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

    public function structure(Request $request)
    {
        // Получаем данные из формы
        $level = $request->level;
        $commissariatId = $request->commissariat_id;
        $departmentId = $request->department_id;
        $divisionId = $request->division_id;

        // Загружаем данные в зависимости от уровня
        if ($level === 'commissariat') {
            $data = Commissariat::with(['departments.divisions'])->findOrFail($commissariatId);
            $type = 'commissariat';
            $name = $data->name;
        } elseif ($level === 'department') {
            $data = Department::with(['commissariat', 'divisions'])->findOrFail($departmentId);
            $type = 'department';
            $name = $data->name;
        } elseif ($level === 'division') {
            $data = Division::with(['commissariat', 'department'])->findOrFail($divisionId);
            $type = 'division';
            $name = $data->name;
        } else {
            return back()->with('error', 'Неверный уровень экспорта');
        }


        // Формируем имя файла
        $fileName = 'structure_'.$name.'_'.date('Y-m-d_H-i-s').'.xlsx';
        $fileName = preg_replace('/[^\w\-\._\(\)]/u', '_', $fileName);

        // Скачиваем файл
        return Excel::download(new StructureExport($data, $type, $name), $fileName);
    }
}
