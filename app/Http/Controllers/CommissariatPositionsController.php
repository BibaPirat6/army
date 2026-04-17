<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;

class CommissariatPositionsController extends Controller
{
    public function index(Request $request)
    {
        $commissariat = Commissariat::findOrFail($request->input('commissariat_id'));

        $commissariatPositions = $commissariat->commissariatPositions()
            ->with(['position.chiefType', 'activeAssignment.employee.person', 'department', 'division'])
            ->get()
            ->sortBy(function ($item) {
                // Создаем вес для сортировки
                return $this->getSortWeight($item);
            })
            ->values(); // Сбрасываем ключи после сортировки

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariat-positions.index', compact('commissariat', 'commissariatPositions', 'backUrl'));
    }

    /**
     * Определяем вес для сортировки по иерархии
     */
    private function getSortWeight($position): int
    {
        if ($position->position?->chiefType?->name === 'начальник комиссариата') {
            return 100;
        }
        if ($position->position?->chiefType?->name === 'начальник отдела') {
            return 200;
        }

        if ($position->position?->chiefType?->name === 'начальник отделения') {
            return 300;
        }

        // Приоритет 6: Остальные сотрудники
        return 400;
    }

    public function create(Request $request)
    {
        $commissariat = Commissariat::findOrFail($request->input('commissariat_id'));
        $departments = Department::where('commissariat_id', $commissariat->id)->get();
        $divisions = Division::where('commissariat_id', $commissariat->id)->get();
        $employees = Employee::all();
        $positions = Position::whereHas('chiefType', function ($query) {
            $query->where('name', 'работник');
        })->get();

        $backUrl = $request->get('back_url');

        return view('admin.org.commissariat-positions.create', compact('commissariat', 'departments', 'divisions', 'employees', 'positions', 'backUrl'));
    }

    public function store(Request $request) {}
}
