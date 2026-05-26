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
            'chief_employee_id' => 'required|integer|min:1|exists:employees,id',
            'chief_position_id' => 'required|integer|min:1|exists:positions,id',
        ]);

        DB::beginTransaction();
        try {
            $division = Division::create([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
            ]);
            $division->refresh();

            // 2) Находим справочную должность
            $chiefPositionRef = Position::where('id', $data['chief_position_id'])->first();
            if (! $chiefPositionRef) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Не найдена должность в справочнике'])->withInput();
            }

            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
                'division_id' => $division->id,
                'position_id' => $chiefPositionRef->id,
                'rate_total' => 1.00,
                'is_independent' => false,
            ]);
            $commissariatPosition->refresh();

            $chiefEmployeeId = $data['chief_employee_id'] ?? null;
            if (! empty($chiefEmployeeId)) {
                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
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
            'chief_employee_id' => 'required|integer|min:1|exists:employees,id',
            'chief_position_id' => 'required|integer|min:1|exists:positions,id',
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

            // 🔍 Ищем слот по НЕИЗМЕНЯЕМЫМ ключам: position + division
            // department_id может быть null — обрабатываем это отдельно
            $chiefSlotQuery = CommissariatPosition::where('position_id', $chiefPositionRef->id)
                ->where('division_id', $division->id);

            // Безопасная проверка department_id (учитываем, что он может быть null)
            if (is_null($oldDepartmentId)) {
                $chiefSlotQuery->whereNull('department_id');
            } else {
                $chiefSlotQuery->where('department_id', $oldDepartmentId);
            }

            $chiefSlot = $chiefSlotQuery->first();

            // 🛡️ Если слот не найден — это критическая ошибка данных, но не падение кода
            if (! $chiefSlot) {
                DB::rollBack();

                return back()->withErrors([
                    'error' => 'Не найден слот должности для этого отделения. Обратитесь к администратору.',
                ])->withInput();
            }

            // Обновляем привязку слота к новому комиссариату/отделу
            $chiefSlot->update([
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
            ]);

            $oldId = (int) ($data['old_chief_employee_id'] ?? 0);
            $newId = (int) $data['chief_employee_id'];

            // =========================
            // СЦЕНАРИЙ 1: статус старого (не меняем сотрудника, только статус)
            // =========================
            if ($oldId && $oldId === $newId) {
                EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                    ->where('employee_id', $oldId)
                    ->update([
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]);

                DB::commit();

                $backUrl = $request->get('backUrl', route('divisions.index'));

                return redirect()->to($backUrl)->with('success', 'Отделение успешно обновлено.');
            }

            // =========================
            // СЦЕНАРИЙ 2: замена начальника
            // =========================
            if ($oldId && $oldId !== $newId) {

                // старому ставим статус увольнения/перевода/и т.д.
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }

                // новому назначение (создаём или обновляем)
                EmployeePosition::updateOrCreate(
                    [
                        'commissariat_position_id' => $chiefSlot->id,
                        'employee_id' => $newId,
                    ],
                    [
                        'rate' => 1.00,
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]
                );
            }

            // =========================
            // СЦЕНАРИЙ 3: не было старого, просто назначаем нового
            // =========================
            if (! $oldId) {
                EmployeePosition::updateOrCreate(
                    [
                        'commissariat_position_id' => $chiefSlot->id,
                        'employee_id' => $newId,
                    ],
                    [
                        'rate' => 1.00,
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]
                );
            }

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
