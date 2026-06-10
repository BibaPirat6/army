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

    // ========== ПРЯМЫЕ ШТАТНЫЕ ДОЛЖНОСТИ КОМИССАРИАТА ==========
    $commissariatPositions = $commissariat->commissariatPositions()
        ->whereNull('department_id')
        ->whereNull('division_id')
        ->with(['position'])
        ->get();

    if ($commissariatPositions->count() > 0) {
        $positionsGroupId = 'group_positions_'.$commissariat->id;
        $nodes[] = [
            'id' => $positionsGroupId,
            'name' => 'Штатные должности',
            'type' => 'group',
            'isGroup' => true,
        ];
        $links[] = ['source' => $commissariatId, 'target' => $positionsGroupId];

        foreach ($commissariatPositions as $position) {
            // Считаем ТОЛЬКО работающих (status_id = 1)
            $occupiedRate = $position->employeePositions()
                ->where('employee_position_status_id', 1)
                ->sum('rate');

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
            $links[] = ['source' => $positionsGroupId, 'target' => $positionId];
        }
    }

    // ========== ОТДЕЛЫ ==========
    $departments = $commissariat->departments;

    foreach ($departments as $department) {
        $deptId = 'department_'.$department->id;
        $nodes[] = [
            'id' => $deptId,
            'name' => $department->name,
            'type' => 'department',
            'url' => route('departments.show', $department->id),
        ];
        $links[] = ['source' => $commissariatId, 'target' => $deptId];

        // Штатные должности отдела
        $departmentPositions = $department->commissariatPositions()
            ->whereNull('division_id')
            ->with(['position'])
            ->get();

        if ($departmentPositions->count() > 0) {
            $deptPositionsGroupId = 'group_dept_positions_'.$department->id;
            $nodes[] = [
                'id' => $deptPositionsGroupId,
                'name' => 'Штатные должности',
                'type' => 'group',
                'isGroup' => true,
            ];
            $links[] = ['source' => $deptId, 'target' => $deptPositionsGroupId];

            foreach ($departmentPositions as $position) {
                // Считаем ТОЛЬКО работающих (status_id = 1)
                $occupiedRate = $position->employeePositions()
                    ->where('employee_position_status_id', 1)
                    ->sum('rate');

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
                $links[] = ['source' => $deptPositionsGroupId, 'target' => $positionId];
            }
        }

        // ========== ОТДЕЛЕНИЯ ОТДЕЛА ==========
        $divisions = $department->divisions;

        foreach ($divisions as $division) {
            $divId = 'division_'.$division->id;
            $nodes[] = [
                'id' => $divId,
                'name' => $division->name,
                'type' => 'division',
                'url' => route('divisions.show', $division->id),
            ];
            $links[] = ['source' => $deptId, 'target' => $divId];

            // Штатные должности отделения
            $divisionPositions = $division->commissariatPositions()
                ->with(['position'])
                ->get();

            if ($divisionPositions->count() > 0) {
                $divPositionsGroupId = 'group_div_positions_'.$division->id;
                $nodes[] = [
                    'id' => $divPositionsGroupId,
                    'name' => 'Штатные должности',
                    'type' => 'group',
                    'isGroup' => true,
                ];
                $links[] = ['source' => $divId, 'target' => $divPositionsGroupId];

                foreach ($divisionPositions as $position) {
                    // Считаем ТОЛЬКО работающих (status_id = 1)
                    $occupiedRate = $position->employeePositions()
                        ->where('employee_position_status_id', 1)
                        ->sum('rate');

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
                    $links[] = ['source' => $divPositionsGroupId, 'target' => $positionId];
                }
            }
        }
    }

    // ========== САМОСТОЯТЕЛЬНЫЕ ОТДЕЛЕНИЯ ==========
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

        // Штатные должности самостоятельного отделения
        $divisionPositions = $division->commissariatPositions()
            ->with(['position'])
            ->get();

        if ($divisionPositions->count() > 0) {
            $divPositionsGroupId = 'group_div_positions_'.$division->id;
            $nodes[] = [
                'id' => $divPositionsGroupId,
                'name' => 'Штатные должности',
                'type' => 'group',
                'isGroup' => true,
            ];
            $links[] = ['source' => $divId, 'target' => $divPositionsGroupId];

            foreach ($divisionPositions as $position) {
                // Считаем ТОЛЬКО работающих (status_id = 1)
                $occupiedRate = $position->employeePositions()
                    ->where('employee_position_status_id', 1)
                    ->sum('rate');

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
                $links[] = ['source' => $divPositionsGroupId, 'target' => $positionId];
            }
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
