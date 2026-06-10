<?php

namespace App\Http\Controllers;

use App\DTO\CommissariatPositionFiltersData;
use App\Filters\CommissariatPositionFilter;
use App\Models\ChiefType;
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
        $commissariat = Commissariat::findOrFail($request->commissariat_id);

        $filters = CommissariatPositionFiltersData::fromRequest($request);

        // Базовый запрос
        $query = CommissariatPosition::query()
            ->where('commissariat_id', $commissariat->id)
            ->with([
                'position.chiefType',
                'department',
                'division',
                'employeePositions' => function ($query) {
                    $query
                        ->with(['employee.person', 'employeePositionStatus'])
                        ->whereIn('employee_position_status_id', [1, 2, 3])
                        ->orderBy('employee_position_status_id');
                },
            ]);

        // Применяем фильтры
        $query->filter(new CommissariatPositionFilter($filters));

        // Получаем все позиции
        $allPositions = $query->get();

        // Фильтрация по статусу вакансии (после загрузки данных)
        if ($filters->vacancyStatus === 'vacant') {
            $allPositions = $allPositions->filter(fn ($p) => $p->has_vacancy);
        } elseif ($filters->vacancyStatus === 'staffed') {
            $allPositions = $allPositions->filter(fn ($p) => ! $p->has_vacancy);
        }

        // Фильтрация по статусу сотрудника (после загрузки данных)
        if ($filters->employeeStatus) {
            $statusMap = ['working' => 1, 'vacation' => 2, 'maternity' => 3];
            $statusId = $statusMap[$filters->employeeStatus] ?? null;

            if ($statusId) {
                $allPositions = $allPositions->filter(function ($position) use ($statusId) {
                    return $position->employeePositions->contains('employee_position_status_id', $statusId);
                });
            }
        }

        // Сортировка
        $sortBy = $filters->sortBy;
        $sortDirection = $filters->sortDirection === 'asc' ? SORT_ASC : SORT_DESC;

        switch ($sortBy) {
            case 'vacancy_status':
                $allPositions = $sortDirection === SORT_ASC
                    ? $allPositions->sortBy(fn ($p) => $p->has_vacancy)
                    : $allPositions->sortByDesc(fn ($p) => $p->has_vacancy);
                break;
            case 'occupied_rate':
                $allPositions = $sortDirection === SORT_ASC
                    ? $allPositions->sortBy(fn ($p) => $p->occupied_rate)
                    : $allPositions->sortByDesc(fn ($p) => $p->occupied_rate);
                break;
            case 'available_rate':
                $allPositions = $sortDirection === SORT_ASC
                    ? $allPositions->sortBy(fn ($p) => $p->available_rate)
                    : $allPositions->sortByDesc(fn ($p) => $p->available_rate);
                break;
            case 'rate_total':
                $allPositions = $sortDirection === SORT_ASC
                    ? $allPositions->sortBy('rate_total')
                    : $allPositions->sortByDesc('rate_total');
                break;
            default:
                $allPositions = $sortDirection === SORT_ASC
                    ? $allPositions->sortBy('id')
                    : $allPositions->sortByDesc('id');
                break;
        }

        // Пагинация
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $paginatedPositions = new \Illuminate\Pagination\LengthAwarePaginator(
            $allPositions->forPage($currentPage, $perPage),
            $allPositions->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Получаем отделения для текущего отдела
        $divisions = Division::query()
            ->where('commissariat_id', $commissariat->id);

        if ($filters->departmentId) {
            $divisions->where('department_id', $filters->departmentId);
        }

        $divisions = $divisions->orderBy('name')->get();

        return view('admin.org.commissariat-positions.index', [
            'commissariat' => $commissariat,
            'commissariatPositions' => $paginatedPositions,
            'filters' => $filters,
            'departments' => Department::query()
                ->where('commissariat_id', $commissariat->id)
                ->orderBy('name')
                ->get(),
            'divisions' => $divisions,
            'chiefTypes' => ChiefType::query()->orderBy('name')->get(),
            'backUrl' => $request->back_url,
            'employeeId' => $request->employeeId,
        ]);
    }

    /**
     * Детальный просмотр штатной должности со всеми назначениями
     */ /**
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
                $query->with(['employee', 'employeePositionStatus'])
                    ->orderBy('id', 'desc');
            },
        ])->findOrFail($id);

        // Получаем общую сумму занятых ставок (только активные, занимающие ставку)
        $occupiedRate = $commissariatPosition->employeePositions()
            ->whereHas('employeePositionStatus', function ($query) {
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
        $employeeId = $request->input('employeeId');

        // Проверяем, существует ли сотрудник с таким ID (если передан)
        if ($employeeId) {
            $employeeExists = Employee::where('id', $employeeId)->exists();
            if (! $employeeExists) {
                $employeeId = null;
            }
        }

        return view('admin.org.commissariat-positions.show', compact(
            'commissariatPosition',
            'hasVacancy',
            'availableRate',
            'occupiedRate',
            'statistics',
            'allStatuses',
            'backUrl',
            'employeeId'
        ));
    }

    public function create(Request $request)
    {
        $commissariat = Commissariat::findOrFail($request->input('commissariat_id'));
        $departments = Department::where('commissariat_id', $commissariat->id)->get();
        $divisions = Division::where('commissariat_id', $commissariat->id)->get();
        $positions = Position::all();

        $backUrl = $request->get('back_url');

        return view('admin.org.commissariat-positions.create', compact(
            'commissariat',
            'departments',
            'divisions',
            'positions',
            'backUrl'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'department_id' => 'sometimes|nullable|integer|min:1|exists:departments,id',
            'division_id' => 'sometimes|nullable|integer|min:1|exists:divisions,id',
            'position_id' => 'required|integer|min:1|exists:positions,id',
            'rate_total' => 'required|numeric|min:0.25|max:2.00',
            'is_independent' => 'required|boolean',
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

            DB::commit();

            $backUrl = $request->get('backUrl', route('commissariat-positions.index'));

            return redirect()->to($backUrl)->with('success', 'Штатная должность успешно создана.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка создания: '.$e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $commissariatPosition = CommissariatPosition::with(['commissariat', 'department', 'division', 'position'])
            ->findOrFail($id);

        $commissariatId = $commissariatPosition->commissariat_id;

        // Фильтруем по комиссариату
        $departments = Department::where('commissariat_id', $commissariatId)->get();
        $divisions = Division::where('commissariat_id', $commissariatId)->get();
        $positions = Position::all();

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariat-positions.edit', compact(
            'commissariatPosition',
            'departments',
            'divisions',
            'positions',
            'backUrl'
        ));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'department_id' => 'sometimes|nullable|integer|min:1|exists:departments,id',
            'division_id' => 'sometimes|nullable|integer|min:1|exists:divisions,id',
            'position_id' => 'required|integer|min:1|exists:positions,id',
            'rate_total' => 'required|numeric|min:0.25|max:2.00',
            'is_independent' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            $commissariatPosition = CommissariatPosition::findOrFail($id);

            // Обновляем основную должность
            $commissariatPosition->update([
                'department_id' => $data['department_id'] ?? null,
                'division_id' => $data['division_id'] ?? null,
                'position_id' => $data['position_id'],
                'rate_total' => $data['rate_total'],
                'is_independent' => $data['is_independent'],
            ]);

            DB::commit();

            $backUrl = $request->get('backUrl', route('commissariat-positions.index'));

            return redirect()->to($backUrl)->with('success', 'Штатная должность успешно обновлена.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка обновления: '.$e->getMessage()])->withInput();
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
