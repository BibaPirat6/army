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

    public function obsidian($id)
    {
        $commissariat = Commissariat::findOrFail($id);

        $nodes = [];
        $links = [];
        $nodeIds = [];

        // Добавляем комиссариат
        $chiefId = 'commissariat_'.$commissariat->id;
        $nodes[] = [
            'id' => $chiefId,
            'name' => $commissariat->name,
            'type' => 'commissariat',
            'url' => route('commissariats.show', $commissariat->id),
        ];

        // Добавляем начальника комиссариата
        $chief = $commissariat->getChiefAttribute();
        if ($chief) {
            $chiefNodeId = 'employee_'.$chief->id;
            $nodes[] = [
                'id' => $chiefNodeId,
                'name' => $chief->getFullNameAttribute(),
                'type' => 'employee',
                'isChief' => true,  // ← ДОБАВИТЬ
                'url' => route('employees.show', $chief->id),
            ];
            $links[] = ['source' => $chiefId, 'target' => $chiefNodeId];
        }

        // ========== СОТРУДНИКИ КОМИССАРИАТА (прямозависящие) ==========
        $employeesNotIndependent = $commissariat->employeesNotIndependent();

        if ($employeesNotIndependent->count() > 0) {
            $commissariatEmployeesGroupId = 'group_commissariat_employees_'.$commissariat->id;
            $nodes[] = [
                'id' => $commissariatEmployeesGroupId,
                'name' => 'Сотрудники',
                'type' => 'group',
                'isGroup' => true,
                'url' => null,
            ];
            $links[] = ['source' => $chiefId, 'target' => $commissariatEmployeesGroupId];

            foreach ($employeesNotIndependent as $employee) {
                $empId = 'employee_'.$employee->id;
                if (! in_array($empId, array_column($nodes, 'id'))) {
                    $nodes[] = [
                        'id' => $empId,
                        'name' => $employee->getFullNameAttribute(),
                        'type' => 'employee',
                        'isChief' => false,  // ← ДОБАВИТЬ (обычный сотрудник)
                        'url' => route('employees.show', $employee->id),
                    ];
                }
                $links[] = ['source' => $commissariatEmployeesGroupId, 'target' => $empId];
            }
        }

        // Добавляем отделы
        foreach ($commissariat->departments as $department) {
            $deptId = 'department_'.$department->id;
            $nodes[] = [
                'id' => $deptId,
                'name' => $department->name,
                'type' => 'department',
                'url' => route('departments.show', $department->id),
            ];
            $links[] = ['source' => $chiefId, 'target' => $deptId];

            // Начальник отдела
            $deptChief = $department->getChiefAttribute();
            if ($deptChief) {
                $deptChiefNodeId = 'employee_'.$deptChief->id;
                if (! in_array($deptChiefNodeId, array_column($nodes, 'id'))) {
                    $nodes[] = [
                        'id' => $deptChiefNodeId,
                        'name' => $deptChief->getFullNameAttribute(),
                        'type' => 'employee',
                        'isChief' => true,  // ← ДОБАВИТЬ
                        'url' => route('employees.show', $deptChief->id),
                    ];
                }
                $links[] = ['source' => $deptId, 'target' => $deptChiefNodeId];
            }

            // ========== СОТРУДНИКИ ОТДЕЛА (прямозависящие) ==========
            $departmentEmployees = $department->employeePositions()
                ->whereNull('division_id')
                ->where('is_independent', 0)
                ->where('employee_id', '!=', optional($deptChief)->id)
                ->with('employee')
                ->get()
                ->map(function ($pos) {
                    return $pos->employee;
                })
                ->filter()
                ->unique('id')
                ->values();

            if ($departmentEmployees->count() > 0) {
                $departmentEmployeesGroupId = 'group_department_employees_'.$department->id;
                $nodes[] = [
                    'id' => $departmentEmployeesGroupId,
                    'name' => 'Сотрудники',
                    'type' => 'group',
                    'isGroup' => true,
                    'url' => null,
                ];
                $links[] = ['source' => $deptId, 'target' => $departmentEmployeesGroupId];

                foreach ($departmentEmployees as $employee) {
                    $empId = 'employee_'.$employee->id;
                    if (! in_array($empId, array_column($nodes, 'id'))) {
                        $nodes[] = [
                            'id' => $empId,
                            'name' => $employee->getFullNameAttribute(),
                            'type' => 'employee',
                            'isChief' => false,  // ← ДОБАВИТЬ (обычный сотрудник)
                            'url' => route('employees.show', $employee->id),
                        ];
                    }
                    $links[] = ['source' => $departmentEmployeesGroupId, 'target' => $empId];
                }
            }

            // Отделения отдела
            foreach ($department->divisions as $division) {
                $divId = 'division_'.$division->id;
                $nodes[] = [
                    'id' => $divId,
                    'name' => $division->name,
                    'type' => 'division',
                    'url' => route('divisions.show', $division->id),
                ];
                $links[] = ['source' => $deptId, 'target' => $divId];

                // Начальник отделения
                $divChief = $division->getChiefAttribute();
                if ($divChief) {
                    $divChiefNodeId = 'employee_'.$divChief->id;
                    if (! in_array($divChiefNodeId, array_column($nodes, 'id'))) {
                        $nodes[] = [
                            'id' => $divChiefNodeId,
                            'name' => $divChief->getFullNameAttribute(),
                            'type' => 'employee',
                            'isChief' => true,  // ← ДОБАВИТЬ
                            'url' => route('employees.show', $divChief->id),
                        ];
                    }
                    $links[] = ['source' => $divId, 'target' => $divChiefNodeId];
                }

                // ========== СОТРУДНИКИ ОТДЕЛЕНИЯ ==========
                $divisionEmployees = $division->employeePositions
                    ->filter(function ($position) use ($divChief) {
                        return $position->employee && $position->employee->id != optional($divChief)->id;
                    })
                    ->values();

                if ($divisionEmployees->count() > 0) {
                    $divisionEmployeesGroupId = 'group_division_employees_'.$division->id;
                    $nodes[] = [
                        'id' => $divisionEmployeesGroupId,
                        'name' => 'Сотрудники',
                        'type' => 'group',
                        'isGroup' => true,
                        'url' => null,
                    ];
                    $links[] = ['source' => $divId, 'target' => $divisionEmployeesGroupId];

                    foreach ($divisionEmployees as $employeePosition) {
                        $emp = $employeePosition->employee;
                        $empId = 'employee_'.$emp->id;
                        if (! in_array($empId, array_column($nodes, 'id'))) {
                            $nodes[] = [
                                'id' => $empId,
                                'name' => $emp->getFullNameAttribute(),
                                'type' => 'employee',
                                'isChief' => false,  // ← ДОБАВИТЬ (обычный сотрудник)
                                'url' => route('employees.show', $emp->id),
                            ];
                        }
                        $links[] = ['source' => $divisionEmployeesGroupId, 'target' => $empId];
                    }
                }
            }
        }

        // ========== САМОСТОЯТЕЛЬНЫЕ СОТРУДНИКИ ==========
        $employeesIndependent = $commissariat->employeesIndependent();

        if ($employeesIndependent->count() > 0) {
            $independentEmployeesGroupId = 'group_independent_employees_'.$commissariat->id;
            $nodes[] = [
                'id' => $independentEmployeesGroupId,
                'name' => 'Cотрудники',
                'type' => 'group',
                'isGroup' => true,
                'url' => null,
            ];
            $links[] = ['source' => $chiefId, 'target' => $independentEmployeesGroupId];

            foreach ($employeesIndependent as $employee) {
                $empId = 'employee_'.$employee->id;
                if (! in_array($empId, array_column($nodes, 'id'))) {
                    $nodes[] = [
                        'id' => $empId,
                        'name' => $employee->getFullNameAttribute(),
                        'type' => 'employee',
                        'isChief' => false,  // ← ДОБАВИТЬ (самостоятельный сотрудник)
                        'url' => route('employees.show', $employee->id),
                    ];
                }
                $links[] = ['source' => $independentEmployeesGroupId, 'target' => $empId];
            }
        }

        // ========== САМОСТОЯТЕЛЬНЫЕ ОТДЕЛЕНИЯ ==========
        $divisionsIndependent = $commissariat->divisionsIntependent();

        foreach ($divisionsIndependent as $division) {
            $divId = 'division_'.$division->id;
            $nodes[] = [
                'id' => $divId,
                'name' => $division->name,
                'type' => 'division',
                'url' => route('divisions.show', $division->id),
            ];
            $links[] = ['source' => $chiefId, 'target' => $divId];

            // Начальник самостоятельного отделения
            $divChief = $division->getChiefAttribute();
            if ($divChief) {
                $divChiefNodeId = 'employee_'.$divChief->id;
                if (! in_array($divChiefNodeId, array_column($nodes, 'id'))) {
                    $nodes[] = [
                        'id' => $divChiefNodeId,
                        'name' => $divChief->getFullNameAttribute(),
                        'type' => 'employee',
                        'isChief' => true,  // ← ДОБАВИТЬ
                        'url' => route('employees.show', $divChief->id),
                    ];
                }
                $links[] = ['source' => $divId, 'target' => $divChiefNodeId];
            }

            // Сотрудники самостоятельного отделения
            $divisionEmployees = $division->employeePositions
                ->filter(function ($position) use ($divChief) {
                    return $position->employee && $position->employee->id != optional($divChief)->id;
                })
                ->values();

            if ($divisionEmployees->count() > 0) {
                $divisionEmployeesGroupId = 'group_division_employees_'.$division->id;
                $nodes[] = [
                    'id' => $divisionEmployeesGroupId,
                    'name' => 'Сотрудники',
                    'type' => 'group',
                    'isGroup' => true,
                    'url' => null,
                ];
                $links[] = ['source' => $divId, 'target' => $divisionEmployeesGroupId];

                foreach ($divisionEmployees as $employeePosition) {
                    $emp = $employeePosition->employee;
                    $empId = 'employee_'.$emp->id;
                    if (! in_array($empId, array_column($nodes, 'id'))) {
                        $nodes[] = [
                            'id' => $empId,
                            'name' => $emp->getFullNameAttribute(),
                            'type' => 'employee',
                            'isChief' => false,  // ← ДОБАВИТЬ (обычный сотрудник)
                            'url' => route('employees.show', $emp->id),
                        ];
                    }
                    $links[] = ['source' => $divisionEmployeesGroupId, 'target' => $empId];
                }
            }
        }

        return view('admin.org.structure.obsidian', [
            'commissariat' => $commissariat,
            'graphData' => [
                'nodes' => $nodes,
                'links' => $links,
            ],
        ]);
    }
}
