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
    $commissariat = Commissariat::with([
        'departments.divisions.employeePositions.employee.person',
        'divisionsIntependent.employeePositions.employee.person'
    ])->findOrFail($id);
    
    // Формируем данные для графа
    $nodes = [];
    $links = [];
    
    // Добавляем начальника комиссариата
    $chiefId = 'commissariat_' . $commissariat->id;
    $nodes[] = [
        'id' => $chiefId,
        'name' => optional($commissariat->getChiefAttribute())?->getFullNameAttribute() ?? 'Начальник не назначен',
        'type' => 'commissariat',
        'url' => route('commissariats.show', $commissariat->id),
    ];
    
    // Добавляем отделы
    foreach ($commissariat->departments as $department) {
        $deptId = 'department_' . $department->id;
        $nodes[] = [
            'id' => $deptId,
            'name' => $department->name,
            'type' => 'department',
            'url' => route('departments.show', $department->id),
        ];
        $links[] = ['source' => $chiefId, 'target' => $deptId];
        
        // Добавляем отделения
        foreach ($department->divisions as $division) {
            $divId = 'division_' . $division->id;
            $nodes[] = [
                'id' => $divId,
                'name' => $division->name,
                'type' => 'division',
                'url' => route('divisions.show', $division->id),
            ];
            $links[] = ['source' => $deptId, 'target' => $divId];
            
            // Добавляем сотрудников отделения
            foreach ($division->employeePositions as $empPos) {
                $empId = 'employee_' . $empPos->employee->id;
                if (!in_array($empId, array_column($nodes, 'id'))) {
                    $nodes[] = [
                        'id' => $empId,
                        'name' => $empPos->employee->getFullNameAttribute(),
                        'type' => 'employee',
                        'url' => route('employees.show', $empPos->employee->id),
                    ];
                }
                $links[] = ['source' => $divId, 'target' => $empId];
            }
        }
    }
    
    // Добавляем сотрудников комиссариата (не самостоятельные)
    $employeesNotIndependent = $commissariat->employeesNotIndependent();
    foreach ($employeesNotIndependent as $employee) {
        $empId = 'employee_' . $employee->id;
        if (!in_array($empId, array_column($nodes, 'id'))) {
            $nodes[] = [
                'id' => $empId,
                'name' => $employee->getFullNameAttribute(),
                'type' => 'employee',
                'url' => route('employees.show', $employee->id),
            ];
        }
        $links[] = ['source' => $chiefId, 'target' => $empId];
    }
    
    // Добавляем самостоятельных сотрудников
    $employeesIndependent = $commissariat->employeesIndependent();
    foreach ($employeesIndependent as $employee) {
        $empId = 'employee_' . $employee->id;
        if (!in_array($empId, array_column($nodes, 'id'))) {
            $nodes[] = [
                'id' => $empId,
                'name' => $employee->getFullNameAttribute(),
                'type' => 'employee',
                'url' => route('employees.show', $employee->id),
            ];
        }
        $links[] = ['source' => $chiefId, 'target' => $empId];
    }
    
    // Добавляем самостоятельные отделения
    $divisionsIndependent = $commissariat->divisionsIntependent();
    foreach ($divisionsIndependent as $division) {
        $divId = 'division_' . $division->id;
        if (!in_array($divId, array_column($nodes, 'id'))) {
            $nodes[] = [
                'id' => $divId,
                'name' => $division->name,
                'type' => 'division',
                'url' => route('divisions.show', $division->id),
            ];
        }
        $links[] = ['source' => $chiefId, 'target' => $divId];
        
        // Добавляем сотрудников самостоятельного отделения
        foreach ($division->employeePositions as $empPos) {
            $empId = 'employee_' . $empPos->employee->id;
            if (!in_array($empId, array_column($nodes, 'id'))) {
                $nodes[] = [
                    'id' => $empId,
                    'name' => $empPos->employee->getFullNameAttribute(),
                    'type' => 'employee',
                    'url' => route('employees.show', $empPos->employee->id),
                ];
            }
            $links[] = ['source' => $divId, 'target' => $empId];
        }
    }
    
    return view('admin.org.structure.show', [
        'commissariat' => $commissariat,
        'graphData' => [
            'nodes' => $nodes,
            'links' => $links,
        ],
    ]);
}
}
