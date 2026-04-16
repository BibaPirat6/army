<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $departments = Department::paginate(50);

        return view('admin.org.departments.index', compact('departments'));
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
            'chief_employee_id' => 'required|integer|min:1|exists:employees,id',
            'chief_position_id' => 'required|integer|min:1|exists:positions,id',
        ]);

        DB::beginTransaction();
        try {
            $department = Department::create([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
            ]);
            $department->refresh();

            // 2) Находим справочную должность
            $chiefPositionRef = Position::where('id', $data['chief_position_id'])->first();
            if (! $chiefPositionRef) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Не найдена должность в справочнике'])->withInput();
            }

            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $department->id,
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

            $backUrl = $request->get('backUrl', route('commissariats.index'));

            return redirect()->to($backUrl)->with('success', 'Комиссариат успешно создан.');

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
            'chief_employee_id' => 'required|integer|exists:employees,id',
            'chief_position_id' => 'required|integer|exists:positions,id',
            'old_chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'old_chief_employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
        ]);
        DB::beginTransaction();
        try {
            $department = Department::findOrFail($id);

               $oldCommissariatId = $department->commissariat_id;

            // Обновляем отдел
            $department->update([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
            ]);

            // Находим справочную должность "начальник отдела"
            $chiefPositionRef = Position::where('id', $data['chief_position_id'])->first();
            if (! $chiefPositionRef) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Не найдена должность в справочнике'])->withInput();
            }

            // Получаем или создаём слот начальника для этого отдела
            $chiefSlot = CommissariatPosition::where([
                'commissariat_id' => $oldCommissariatId,
                'position_id' => $chiefPositionRef->id,
                'department_id' => $department->id,
                'division_id' => null,
            ])->first();

            $chiefSlot->update([
                'commissariat_id'=>$data['commissariat_id']
            ]);

            $oldId = (int) $data['old_chief_employee_id'];
            $newId = (int) $data['chief_employee_id'];

            // =========================
            // СЦЕНАРИЙ 1: статус старого
            // =========================
            if ($oldId && $oldId === $newId) {
                EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                    ->where('employee_id', $oldId)
                    ->update([
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]);

                DB::commit();

                $backUrl = $request->get('backUrl', route('departments.index'));

                return redirect()->to($backUrl)->with('success', 'Отдел успешно обновлен.');
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

                // новому назначение
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
