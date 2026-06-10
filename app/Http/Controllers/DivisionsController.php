<?php

namespace App\Http\Controllers;

use App\DTO\DivisionFiltersData;
use App\Filters\DivisionFilter;
use App\Models\Commissariat;
use App\Models\CommissariatPosition;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePositionStatus;
use App\Models\Person;
use App\Models\Position;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisionsController extends Controller
{
    public function index(Request $request)
    {
        $filters = DivisionFiltersData::fromRequest(
            $request
        );

        $divisions = Division::query()

            ->with([
                'commissariat',
                'department',
            ])

            ->filter(
                new DivisionFilter($filters)
            )

            ->paginate(15)

            ->withQueryString();

        return view(
            'admin.org.divisions.index',
            [
                'divisions' => $divisions,

                'filters' => $filters,

                'commissariats' => Commissariat::query()
                    ->orderBy('name')
                    ->get(),

                'departments' => Department::query()
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }

    public function show(Request $request, $id)
    {
        $division = Division::findOrFail($id);
        $backUrl = $request->input('back_url');
        $columns = Person::getTableColumns();

        return view('admin.org.divisions.show', compact('division', 'backUrl', 'columns'));
    }

    public function create(Request $request)
    {
        $commissariats = Commissariat::all();
        $departments = Department::all();

        $employees = Employee::all();

        $commissariatId = $request->get('commissariat_id');
        $commissariat = $commissariatId
            ? Commissariat::find($commissariatId)
            : null;

        $departmentId = $request->get('department_id');
        $department = $departmentId
            ? Department::find($departmentId)
            : null;

        $backUrl = $request->get('back_url');

        $positions = Position::whereHas('chiefType', function ($query) {
            $query->where('name', 'начальник отделения');
        })->get();

        return view('admin.org.divisions.create', compact('commissariats', 'departments', 'employees', 'commissariat', 'backUrl', 'department', 'positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'department_id' => [
                'nullable',
                'sometimes',
                Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],
            'chief_position_id' => 'required|integer|min:1|exists:positions,id', // ОБЯЗАТЕЛЬНОЕ
            'chief_employee_id' => 'nullable|integer|min:1|exists:employees,id', // НЕобязательное
        ]);

        DB::beginTransaction();
        try {
            $division = Division::create([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
            ]);
            $division->refresh();

            // Всегда создаём штатную должность начальника
            $chiefPositionRef = Position::findOrFail($data['chief_position_id']);

            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
                'division_id' => $division->id,
                'position_id' => $chiefPositionRef->id,
                'rate_total' => 1.00,
                'is_independent' => false,
            ]);

            // Назначаем сотрудника только если он выбран
            if (! empty($data['chief_employee_id'])) {
                EmployeePosition::create([
                    'employee_id' => $data['chief_employee_id'],
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1, // работает
                ]);
            }

            DB::commit();
            $backUrl = $request->get('backUrl', route('divisions.index'));

            return redirect()->to($backUrl)->with('success', 'Отделение успешно создано.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка создания: '.$e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $commissariats = Commissariat::all();
        $departments = Department::all();
        $division = Division::with('commissariat', 'department')->findOrFail($id);
        $employees = Employee::all();
        $positions = Position::whereHas('chiefType', function ($q) {
            $q->where('name', 'начальник отделения');
        })->get();

        $employeePositionStatuses = EmployeePositionStatus::all();

        $backUrl = $request->input('back_url');

        return view('admin.org.divisions.edit', compact('division', 'commissariats', 'departments', 'employees', 'backUrl', 'positions', 'employeePositionStatuses'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'department_id' => [
                'nullable',
                'sometimes',
                Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],
            'chief_position_id' => 'required|integer|min:1|exists:positions,id', // ОБЯЗАТЕЛЬНОЕ
            'chief_employee_id' => 'nullable|integer|min:1|exists:employees,id', // НЕобязательное
            'old_chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'old_chief_employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
        ]);

        DB::beginTransaction();
        try {
            $division = Division::findOrFail($id);
            $oldCommissariatId = $division->commissariat_id;
            $oldDepartmentId = $division->department_id ?? null;

            // Обновляем отдел
            $division->update([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
            ]);

            // Находим справочную должность
            $chiefPositionRef = Position::findOrFail($data['chief_position_id']);

            // Ищем существующий слот должности начальника
            $chiefSlotQuery = CommissariatPosition::where('position_id', $chiefPositionRef->id)
                ->where('division_id', $division->id);

            if (is_null($oldDepartmentId)) {
                $chiefSlotQuery->whereNull('department_id');
            } else {
                $chiefSlotQuery->where('department_id', $oldDepartmentId);
            }

            $chiefSlot = $chiefSlotQuery->first();

            // Если слот не найден — создаём новый
            if (! $chiefSlot) {
                $chiefSlot = CommissariatPosition::create([
                    'commissariat_id' => $data['commissariat_id'],
                    'department_id' => $data['department_id'] ?? null,
                    'division_id' => $division->id,
                    'position_id' => $chiefPositionRef->id,
                    'rate_total' => 1.00,
                    'is_independent' => false,
                ]);
            } else {
                // Обновляем привязку слота
                $chiefSlot->update([
                    'commissariat_id' => $data['commissariat_id'],
                    'department_id' => $data['department_id'] ?? null,
                ]);
            }

            $oldId = (int) ($data['old_chief_employee_id'] ?? 0);
            $newId = (int) ($data['chief_employee_id'] ?? 0);

            // =========================
            // СЦЕНАРИЙ 1: Снимаем начальника (очистили поле)
            // =========================
            if ($oldId && ! $newId) {
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }
            }
            // =========================
            // СЦЕНАРИЙ 2: Меняем начальника
            // =========================
            elseif ($oldId && $newId && $oldId !== $newId) {
                // Старому ставим статус
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }

                // Новому создаём назначение
                EmployeePosition::updateOrCreate(
                    [
                        'commissariat_position_id' => $chiefSlot->id,
                        'employee_id' => $newId,
                    ],
                    [
                        'rate' => 1.00,
                        'employee_position_status_id' => 1,
                    ]
                );
            }
            // =========================
            // СЦЕНАРИЙ 3: Назначаем нового (старого не было)
            // =========================
            elseif (! $oldId && $newId) {
                EmployeePosition::updateOrCreate(
                    [
                        'commissariat_position_id' => $chiefSlot->id,
                        'employee_id' => $newId,
                    ],
                    [
                        'rate' => 1.00,
                        'employee_position_status_id' => 1,
                    ]
                );
            }
            // СЦЕНАРИЙ 4: Не было и не назначили — ничего не делаем

            DB::commit();

            $backUrl = $request->get('backUrl', route('divisions.index'));

            return redirect()->to($backUrl)->with('success', 'Отделение успешно обновлено.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка обновления: '.$e->getMessage()])->withInput();
        }
    }

    public function delete($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();

        return redirect()->route('divisions.index')->with('success', 'Подразделение успешно удалено.');
    }
}
