<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use Illuminate\Http\Request;

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

    public function obsidian(Request $request, $id)
    {
        $backUrl = $request->input('back_url');
        $commissariat = Commissariat::findOrFail($id);

        $nodes = [];
        $links = [];

        // ========== 1. КОМИССАРИАТ (ЦЕНТР) ==========
        $commissariatId = 'commissariat_'.$commissariat->id;
        $nodes[] = [
            'id' => $commissariatId,
            'name' => $commissariat->name,
            'type' => 'commissariat',
            'url' => route('commissariats.show', $commissariat->id),
        ];

        // ========== ОБЩИЙ УЗЕЛ ДЛЯ СОТРУДНИКОВ КОМИССАРИАТА ==========
        $employeesGroupId = 'group_employees_'.$commissariat->id;
        $nodes[] = [
            'id' => $employeesGroupId,
            'name' => 'Сотрудники',
            'type' => 'group',
            'isGroup' => true,
        ];
        $links[] = ['source' => $commissariatId, 'target' => $employeesGroupId];

        // ========== ШТАТНЫЕ ДОЛЖНОСТИ КОМИССАРИАТА (прямые, без отдела/отделения) ==========
        $commissariatPositions = $commissariat->commissariatPositions()
            ->whereNull('department_id')
            ->whereNull('division_id')
            ->with(['position', 'employeePositions' => function ($q) {
                $q->whereIn('employee_position_status_id', [1, 2, 3]);
            }])
            ->get();

        foreach ($commissariatPositions as $position) {
            $activeAssignments = $position->employeePositions;
            $occupiedRate = $activeAssignments->sum('rate');
            $availableRate = $position->rate_total - $occupiedRate;
            $isFullyOccupied = $availableRate <= 0;

            $positionId = 'position_'.$position->id;
            $nodes[] = [
                'id' => $positionId,
                'name' => $position->position->name,
                'type' => 'position',
                'isFullyOccupied' => $isFullyOccupied,
                'availableRate' => $availableRate,
                'totalRate' => $position->rate_total,
                'occupiedRate' => $occupiedRate,
                'color' => $isFullyOccupied ? '#4CAF50' : '#F44336',
                'url' => route('commissariat-positions.show', $position->id),
                'commissariatId' => $commissariat->id,
            ];
            $links[] = ['source' => $employeesGroupId, 'target' => $positionId];
        }

        // ========== ОТДЕЛЫ ==========
        foreach ($commissariat->departments as $department) {
            $deptId = 'department_'.$department->id;
            $nodes[] = [
                'id' => $deptId,
                'name' => $department->name,
                'type' => 'department',
                'url' => route('departments.show', $department->id),
            ];
            $links[] = ['source' => $commissariatId, 'target' => $deptId];

            // Узел сотрудников отдела
            $deptEmployeesGroupId = 'group_dept_employees_'.$department->id;
            $nodes[] = [
                'id' => $deptEmployeesGroupId,
                'name' => 'Сотрудники',
                'type' => 'group',
                'isGroup' => true,
            ];
            $links[] = ['source' => $deptId, 'target' => $deptEmployeesGroupId];

            // Штатные должности отдела (без отделения)
            $departmentPositions = $department->commissariatPositions()
                ->whereNull('division_id')
                ->with(['position', 'employeePositions' => function ($q) {
                    $q->whereIn('employee_position_status_id', [1, 2, 3]);
                }])
                ->get();

            foreach ($departmentPositions as $position) {
                $activeAssignments = $position->employeePositions;
                $occupiedRate = $activeAssignments->sum('rate');
                $availableRate = $position->rate_total - $occupiedRate;
                $isFullyOccupied = $availableRate <= 0;

                $positionId = 'position_'.$position->id;
                $nodes[] = [
                    'id' => $positionId,
                    'name' => $position->position->name,
                    'type' => 'position',
                    'isFullyOccupied' => $isFullyOccupied,
                    'availableRate' => $availableRate,
                    'totalRate' => $position->rate_total,
                    'occupiedRate' => $occupiedRate,
                    'color' => $isFullyOccupied ? '#4CAF50' : '#F44336',
                    'url' => route('commissariat-positions.show', $position->id),
                    'commissariatId' => $commissariat->id,
                ];
                $links[] = ['source' => $deptEmployeesGroupId, 'target' => $positionId];
            }

            // ========== ОТДЕЛЕНИЯ ОТДЕЛА ==========
            foreach ($department->divisions as $division) {
                $divId = 'division_'.$division->id;
                $nodes[] = [
                    'id' => $divId,
                    'name' => $division->name,
                    'type' => 'division',
                    'url' => route('divisions.show', $division->id),
                ];
                $links[] = ['source' => $deptId, 'target' => $divId];

                // Узел сотрудников отделения
                $divEmployeesGroupId = 'group_div_employees_'.$division->id;
                $nodes[] = [
                    'id' => $divEmployeesGroupId,
                    'name' => 'Сотрудники',
                    'type' => 'group',
                    'isGroup' => true,
                ];
                $links[] = ['source' => $divId, 'target' => $divEmployeesGroupId];

                // Штатные должности отделения
                $divisionPositions = $division->commissariatPositions()
                    ->with(['position', 'employeePositions' => function ($q) {
                        $q->whereIn('employee_position_status_id', [1, 2, 3]);
                    }])
                    ->get();

                foreach ($divisionPositions as $position) {
                    $activeAssignments = $position->employeePositions;
                    $occupiedRate = $activeAssignments->sum('rate');
                    $availableRate = $position->rate_total - $occupiedRate;
                    $isFullyOccupied = $availableRate <= 0;

                    $positionId = 'position_'.$position->id;
                    $nodes[] = [
                        'id' => $positionId,
                        'name' => $position->position->name,
                        'type' => 'position',
                        'isFullyOccupied' => $isFullyOccupied,
                        'availableRate' => $availableRate,
                        'totalRate' => $position->rate_total,
                        'occupiedRate' => $occupiedRate,
                        'color' => $isFullyOccupied ? '#4CAF50' : '#F44336',
                        'url' => route('commissariat-positions.show', $position->id),
                        'commissariatId' => $commissariat->id,
                    ];
                    $links[] = ['source' => $divEmployeesGroupId, 'target' => $positionId];
                }
            }
        }

        // ========== САМОСТОЯТЕЛЬНЫЕ ОТДЕЛЕНИЯ (вне отделов) ==========
        $divisionsIndependent = $commissariat->divisions()->whereNull('department_id')->get();

        foreach ($divisionsIndependent as $division) {
            $divId = 'division_independent_'.$division->id;
            $nodes[] = [
                'id' => $divId,
                'name' => $division->name,
                'type' => 'division',
                'isIndependent' => true,
                'url' => route('divisions.show', $division->id),
            ];
            $links[] = ['source' => $commissariatId, 'target' => $divId];

            // Узел сотрудников самостоятельного отделения
            $divEmployeesGroupId = 'group_div_employees_'.$division->id;
            $nodes[] = [
                'id' => $divEmployeesGroupId,
                'name' => 'Сотрудники',
                'type' => 'group',
                'isGroup' => true,
            ];
            $links[] = ['source' => $divId, 'target' => $divEmployeesGroupId];

            // Штатные должности самостоятельного отделения
            $divisionPositions = $division->commissariatPositions()
                ->with(['position', 'employeePositions' => function ($q) {
                    $q->whereIn('employee_position_status_id', [1, 2, 3]);
                }])
                ->get();

            foreach ($divisionPositions as $position) {
                $activeAssignments = $position->employeePositions;
                $occupiedRate = $activeAssignments->sum('rate');
                $availableRate = $position->rate_total - $occupiedRate;
                $isFullyOccupied = $availableRate <= 0;

                $positionId = 'position_'.$position->id;
                $nodes[] = [
                    'id' => $positionId,
                    'name' => $position->position->name,
                    'type' => 'position',
                    'isFullyOccupied' => $isFullyOccupied,
                    'availableRate' => $availableRate,
                    'totalRate' => $position->rate_total,
                    'occupiedRate' => $occupiedRate,
                    'color' => $isFullyOccupied ? '#4CAF50' : '#F44336',
                    'url' => route('commissariat-positions.show', $position->id),
                    'commissariatId' => $commissariat->id,
                ];
                $links[] = ['source' => $divEmployeesGroupId, 'target' => $positionId];
            }
        }

        return view('admin.org.structure.obsidian', [
            'commissariat' => $commissariat,
            'graphData' => [
                'nodes' => $nodes,
                'links' => $links,
            ],
            'backUrl' => $backUrl,
        ]);
    }
}
