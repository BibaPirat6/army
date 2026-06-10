<?php

namespace App\Http\Controllers;

use App\DTO\DepartmentFiltersData;
use App\Filters\DepartmentFilter;
use App\Models\Commissariat;
use App\Models\CommissariatPosition;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePositionStatus;
use App\Models\Person;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentsController extends Controller
{
    public function index(Request $request)
    {
        $filters = DepartmentFiltersData::fromRequest(
            $request
        );

        $departments = Department::query()

            ->with([
                'commissariat',
            ])

            ->filter(
                new DepartmentFilter($filters)
            )

            ->paginate(15)

            ->withQueryString();

        return view(
            'admin.org.departments.index',
            [
                'departments' => $departments,

                'filters' => $filters,

                'commissariats' => Commissariat::query()
                    ->orderBy('name')
                    ->get(),
            ]
        );
    }

    public function show(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        $backUrl = $request->input('back_url');
        $columns = Person::getTableColumns();

        return view('admin.org.departments.show', compact('department', 'backUrl', 'columns'));
    }

    public function create(Request $request)
    {
        $commissariats = Commissariat::all();
        $employees = Employee::all();

        $commissariatId = $request->get('commissariat_id');
        $commissariat = $commissariatId
            ? Commissariat::find($commissariatId)
            : null;

        $employeePositionStatuses = EmployeePositionStatus::all();

        $positions = Position::whereHas('chiefType', function ($query) {
            $query->where('name', 'начальник отдела');
        })->get();

        $backUrl = $request->get('back_url');

        return view('admin.org.departments.create', compact('commissariats', 'employees', 'commissariat', 'backUrl', 'positions', 'employeePositionStatuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'chief_employee_id' => 'nullable|integer|min:1|exists:employees,id', // ✅ changed from 'required' to 'nullable'
            'chief_position_id' => 'required|integer|min:1|exists:positions,id',
        ]);

        DB::beginTransaction();
        try {
            $department = Department::create([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
            ]);
            $department->refresh();

            $chiefPositionRef = Position::where('id', $data['chief_position_id'])->first();
            if (! $chiefPositionRef) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Не найдена должность в справочнике'])->withInput();
            }

            // ✅ ВСЕГДА создаём штатную должность начальника отдела
            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $department->id,
                'position_id' => $chiefPositionRef->id,
                'rate_total' => 1.00,
                'is_independent' => false,
            ]);
            $commissariatPosition->refresh();

            // ✅ Назначаем начальника ТОЛЬКО если выбран сотрудник
            $chiefEmployeeId = $data['chief_employee_id'] ?? null;
            if (! empty($chiefEmployeeId)) {
                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1,
                ]);
            }

            DB::commit();

            $backUrl = $request->get('backUrl', route('commissariats.index'));

            return redirect()->to($backUrl)->with('success', 'Отдел успешно создан.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка создания: '.$e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $department = Department::with('commissariat')->findOrFail($id);
        $commissariats = Commissariat::all();
        $employees = Employee::all();
        // позиции с типом руководителя "начальник отдела"
        $positions = Position::whereHas('chiefType', function ($q) {
            $q->where('name', 'начальник отдела');
        })->get();

        $employeePositionStatuses = EmployeePositionStatus::all();

        $backUrl = $request->input('back_url');

        return view('admin.org.departments.edit', compact('department', 'commissariats', 'employees', 'backUrl', 'positions', 'employeePositionStatuses'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'chief_employee_id' => 'nullable|integer|exists:employees,id',
            'chief_position_id' => 'required|integer|min:1|exists:positions,id', // ✅ обязательно
            'old_chief_employee_id' => 'nullable|integer|exists:employees,id',
            'old_chief_employee_position_status_id' => 'nullable|integer|exists:employee_position_statuses,id',
        ]);

        DB::beginTransaction();
        try {
            $department = Department::findOrFail($id);

            // ✅ Используем переданный ID должности
            $chiefPositionRef = Position::find($data['chief_position_id']);

            if (! $chiefPositionRef) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Не найдена должность в справочнике'])->withInput();
            }

            // Находим или создаём штатную должность для начальника
            $chiefSlot = CommissariatPosition::firstOrCreate(
                [
                    'department_id' => $department->id,
                    'position_id' => $chiefPositionRef->id,
                    'division_id' => null,
                ],
                [
                    'commissariat_id' => $data['commissariat_id'],
                    'rate_total' => 1.00,
                    'is_independent' => false,
                ]
            );

            // Обновляем комиссариат у штатной должности если нужно
            if ($chiefSlot->commissariat_id != $data['commissariat_id']) {
                $chiefSlot->update(['commissariat_id' => $data['commissariat_id']]);
            }

            // Обновляем отдел
            $department->update([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
            ]);

            $oldId = ! empty($data['old_chief_employee_id']) ? (int) $data['old_chief_employee_id'] : null;
            $newId = ! empty($data['chief_employee_id']) ? (int) $data['chief_employee_id'] : null;

            // Сценарии обработки
            if (! $oldId && $newId) {
                // Назначаем первого начальника
                EmployeePosition::create([
                    'employee_id' => $newId,
                    'commissariat_position_id' => $chiefSlot->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1,
                ]);
            } elseif ($oldId && ! $newId) {
                // Удаляем начальника - меняем статус
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }
            } elseif ($oldId && $newId && $oldId !== $newId) {
                // Замена начальника
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }

                // Создаём новое назначение
                EmployeePosition::create([
                    'employee_id' => $newId,
                    'commissariat_position_id' => $chiefSlot->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1,
                ]);
            } elseif ($oldId && $newId && $oldId === $newId) {
                // Ничего не меняется, но если нужно обновить статус
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }
            }

            DB::commit();

            $backUrl = $request->get('backUrl', route('departments.index'));

            return redirect()->to($backUrl)->with('success', 'Отдел успешно обновлён.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка обновления: '.$e->getMessage()])->withInput();
        }
    }

    public function delete($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Отдел успешно удален.');
    }
}
