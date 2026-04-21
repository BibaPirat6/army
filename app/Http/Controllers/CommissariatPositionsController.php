<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\CommissariatPosition;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePositionStatus;
use App\Models\Position;
use DB;
use Illuminate\Http\Request;

class CommissariatPositionsController extends Controller
{
    public function index(Request $request)
    {
        $commissariat = Commissariat::findOrFail($request->input('commissariat_id'));

        $commissariatPositions = $commissariat->commissariatPositions()
            ->with([
                'position.chiefType',
                'department',
                'division',
                'employeePositions' => function ($query) {
                    // Загружаем только активных сотрудников (которые занимают ставку)
                    $query->with(['employee', 'employeePositionStatus'])
                        ->whereHas('employeePositionStatus', function ($q) {
                            $q->where('occupies_rate', true);
                        })
                        ->orderBy('id', 'desc');
                },
                'employeePositions.employee',
            ])
            ->get()
            ->sortBy(function ($item) {
                return $this->getSortWeight($item);
            })
            ->values();

        // Добавляем вычисляемые поля для каждой должности
        foreach ($commissariatPositions as $position) {
            // Сумма всех ставок занятых активными сотрудниками
            $position->occupied_rate = $position->employeePositions->sum('rate');
            // Свободные ставки
            $position->available_rate = $position->rate_total - $position->occupied_rate;
            // Есть ли вакансия
            $position->has_vacancy = $position->available_rate > 0;
            // Список сотрудников для отображения
            $position->employees_list = $position->employeePositions->map(function ($assignment) {
                return [
                    'name' => $assignment->employee->getFullNameAttribute(),
                    'rate' => $assignment->rate,
                    'employee_id' => $assignment->employee->id,
                ];
            });
        }

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

    /**
     * Детальный просмотр штатной должности со всеми назначениями
     */
    public function show(Request $request, $id)
    {
        // Загружаем штатную должность со всеми связями
        $commissariatPosition = CommissariatPosition::with([
            'commissariat',
            'department',
            'division',
            'position',
            'position.positionType',
            'position.chiefType',
            'employeePositions' => function ($query) {
                // Загружаем все назначения с сортировкой по ID
                $query->with(['employee', 'employeePositionStatus']) // Используем правильные названия отношений
                    ->orderBy('id', 'desc');
            },
        ])->findOrFail($id);

        // Получаем общую сумму занятых ставок (только активные, занимающие ставку)
        $occupiedRate = $commissariatPosition->employeePositions()
            ->whereHas('employeePositionStatus', function ($query) { // Используем правильное имя отношения
                $query->where('occupies_rate', true);
            })
            ->sum('rate');

        $availableRate = $commissariatPosition->rate_total - $occupiedRate;

        // Проверяем, есть ли вакансия (доступные ставки)
        $hasVacancy = $availableRate > 0;

        // Получаем все статусы для фильтрации
        $allStatuses = EmployeePositionStatus::all();

        // Статистика по назначениям
        $activeAssignmentsCount = 0;
        foreach ($commissariatPosition->employeePositions as $assignment) {
            if ($assignment->employeePositionStatus && $assignment->employeePositionStatus->occupies_rate) {
                $activeAssignmentsCount++;
            }
        }

        $statistics = [
            'total_assignments' => $commissariatPosition->employeePositions->count(),
            'active_assignments' => $activeAssignmentsCount,
            'occupied_rate' => $occupiedRate,
            'available_rate' => $availableRate,
            'total_rate' => $commissariatPosition->rate_total,
            'vacancy_percent' => $commissariatPosition->rate_total > 0
                ? round(($availableRate / $commissariatPosition->rate_total) * 100, 2)
                : 0,
        ];

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariat-positions.show', compact(
            'commissariatPosition',
            'hasVacancy',
            'availableRate',
            'occupiedRate',
            'statistics',
            'allStatuses',
            'backUrl'
        ));
    }

    public function create(Request $request)
    {
        $commissariat = Commissariat::findOrFail($request->input('commissariat_id'));
        $departments = Department::where('commissariat_id', $commissariat->id)->get();
        $divisions = Division::where('commissariat_id', $commissariat->id)->get();
        $employees = Employee::all();
        // $positions = Position::whereHas('chiefType', function ($query) {
        //     $query->where('name', 'работник');
        // })->get();
        $positions = Position::all();

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

    public function delete(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Находим штатную должность
            $commissariatPosition = CommissariatPosition::findOrFail($id);
            // Удаляем все назначения сотрудников на эту должность
            EmployeePosition::where('commissariat_position_id', $commissariatPosition->id)->delete();

            // Удаляем саму штатную должность
            $commissariatPosition->delete();

            DB::commit();

            $backUrl = $request->get('back_url', route('commissariat-positions.index'));

            return redirect()->to($backUrl)->with('success', 'Штатная должность успешно удалена + удалены сотрудники на этой штатной должности.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка удаления: '.$e->getMessage()])->withInput();
        }
    }

}
