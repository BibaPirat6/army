<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\CommissariatPosition;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Position;
use DB;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'department_id' => 'sometimes|nullable|integer|min:1|exists:departments,id',
            'division_id' => 'sometimes|nullable|integer|min:1|exists:divisions,id',
            'position_id' => 'required|integer|min:1|exists:positions,id',
            'rate_total' => 'required|numeric|min:0.25|max:2.00',
            'is_independent' => 'required|boolean', // 👈 ДОБАВИЛИ
            'chief_employee_id' => 'nullable|integer|exists:employees,id', // 👈 ДОБАВИЛИ
            'rate' => 'nullable|numeric|min:0.25|max:2.00', // 👈 ДОБАВИЛИ
        ]);

        DB::beginTransaction();
        try {
            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
                'division_id' => $data['division_id'] ?? null,
                'position_id' => $data['position_id'],
                'rate_total' => $data['rate_total'],
                'is_independent' => $data['is_independent'],
            ]);
            $commissariatPosition->refresh();

            // Создаем запись о сотруднике ТОЛЬКО если выбран сотрудник
            if (! empty($data['chief_employee_id']) && ! empty($data['rate'])) {
                EmployeePosition::create([
                    'employee_id' => $data['chief_employee_id'],
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => $data['rate'],
                    'employee_position_status_id' => 1,
                ]);
            }

            DB::commit();

            $backUrl = $request->get('backUrl', route('commissariat-positions.index'));

            return redirect()->to($backUrl)->with('success', 'Штатная должность успешно создана.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка создания: '.$e->getMessage()])->withInput();
        }
    }
}
