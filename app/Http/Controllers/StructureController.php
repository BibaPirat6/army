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

        // Добавляем начальника комиссариата как отдельный узел
        $chief = $commissariat->getChiefAttribute();
        if ($chief) {
            $chiefNodeId = 'employee_'.$chief->id;
            $nodes[] = [
                'id' => $chiefNodeId,
                'name' => $chief->getFullNameAttribute(),
                'type' => 'employee',
                'url' => route('employees.show', $chief->id),
            ];
            $links[] = ['source' => $chiefId, 'target' => $chiefNodeId];
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
                        'url' => route('employees.show', $deptChief->id),
                    ];
                }
                $links[] = ['source' => $deptId, 'target' => $deptChiefNodeId];
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
                            'url' => route('employees.show', $divChief->id),
                        ];
                    }
                    $links[] = ['source' => $divId, 'target' => $divChiefNodeId];
                }

                // Сотрудники отделения
                foreach ($division->employeePositions as $empPos) {
                    $emp = $empPos->employee;
                    if ($emp && $emp->id != optional($divChief)->id) {
                        $empId = 'employee_'.$emp->id;
                        if (! in_array($empId, array_column($nodes, 'id'))) {
                            $nodes[] = [
                                'id' => $empId,
                                'name' => $emp->getFullNameAttribute(),
                                'type' => 'employee',
                                'url' => route('employees.show', $emp->id),
                            ];
                        }
                        $links[] = ['source' => $divId, 'target' => $empId];
                    }
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
