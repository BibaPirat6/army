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
            'chief_employee_id' => 'required|sometimes|integer|min:1|exists:employees,id',
            'employee_position_status_id' => 'sometimes|nullable|integer|min:1|exists:employee_position_statuses,id',
            'chief_position_id' => 'required|sometimes|integer|min:1|exists:positions,id',
        ], [
            'name.required' => 'Название отдела обязательно для заполнения.',
            'name.string' => 'Название отдела должно быть строкой.',
            'name.min' => 'Название отдела должно содержать минимум 2 символа.',
            'name.max' => 'Название отдела не должно превышать 255 символов.',
            'commissariat_id.required' => 'Выберите комиссариат',
            'commissariat_id.exists' => 'Несуществующий комиссариат',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
            'chief_position_id.exists' => 'Несуществующая должность',
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
                $statusId = $data['employee_position_status_id'] ?? 1; // fallback to 1

                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => $statusId,
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
            'chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'chief_position_id' => 'sometimes|nullable|integer|exists:positions,id',

            'employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
            'old_chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'old_chief_employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
        ], [
            'name.required' => 'Название отдела обязательно для заполнения.',
            'name.string' => 'Название отдела должно быть строкой.',
            'name.min' => 'Название отдела должно содержать минимум 2 символа.',
            'name.max' => 'Название отдела не должно превышать 255 символов.',
            'commissariat_id.required' => 'Выберите комиссариат',
            'commissariat_id.exists' => 'Несуществующий комиссариат',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
            'chief_position_id.exists' => 'Несуществующая должность',
        ]);

        $department = Department::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1) Обновляем данные отдела
            $department->update([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'] ?? $department->commissariat_id,
            ]);

            // 2) Находим должность "начальник отдела" в справочнике — используем переданный position_id
            $chiefPositionRef = Position::where('id', $data['chief_position_id'])
                ->whereHas('chiefType', function ($q) {
                    $q->where('name', 'начальник отдела');
                })->first();

            if (! $chiefPositionRef) {
                DB::rollBack();

                return back()->withErrors(['error' => 'Не найдена должность "начальник отдела" в справочнике'])->withInput();
            }

            // 3) Ищем или создаём штатную для данного отдела (CommissariatPosition привязан к department)
            $chiefSlot = CommissariatPosition::firstOrCreate([
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $department->id,
                'position_id' => $chiefPositionRef->id,
            ], [
                'rate_total' => 1.00,
                'is_independent' => false,
            ]);
            $chiefSlot->refresh();

            // Управление назначением/сменой начальника.
            // В запросе приходят employee IDs (не IDs employee_position). Нужно искать записи
            // EmployeePosition по паре (commissariat_position_id, employee_id).
            $oldChiefEmployeeId = !empty($data['old_chief_employee_id']) ? (int)$data['old_chief_employee_id'] : null;
            $newChiefEmployeeId = !empty($data['chief_employee_id']) ? (int)$data['chief_employee_id'] : null;

            // Существующие записи назначений для этой штатной
            $assignmentsForSlot = EmployeePosition::where('commissariat_position_id', $chiefSlot->id)->get()->keyBy('employee_id');

            $oldAssignment = $oldChiefEmployeeId && isset($assignmentsForSlot[$oldChiefEmployeeId]) ? $assignmentsForSlot[$oldChiefEmployeeId] : null;
            $newAssignment = $newChiefEmployeeId && isset($assignmentsForSlot[$newChiefEmployeeId]) ? $assignmentsForSlot[$newChiefEmployeeId] : null;

            // Также получим активное назначение для быстрого обновления (если есть)
            $activeAssignment = $chiefSlot->activeAssignment;

            // Сценарии обработки
            if (! $oldChiefEmployeeId && ! $newChiefEmployeeId) {
                // ничего
            } elseif (! $oldChiefEmployeeId && $newChiefEmployeeId) {
                // Ранее не было начальника, назначаем нового
                if ($newAssignment) {
                    $newAssignment->employee_position_status_id = $data['employee_position_status_id'] ?? $newAssignment->employee_position_status_id;
                    $newAssignment->save();
                } else {
                    EmployeePosition::create([
                        'employee_id' => $newChiefEmployeeId,
                        'commissariat_position_id' => $chiefSlot->id,
                        'rate' => 1.00,
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]);
                }
            } elseif ($oldChiefEmployeeId && ! $newChiefEmployeeId) {
                // Был начальник, теперь не назначают — обновляем его статус, если передан
                if ($oldAssignment) {
                    if (isset($data['old_chief_employee_position_status_id'])) {
                        $oldAssignment->employee_position_status_id = $data['old_chief_employee_position_status_id'];
                        $oldAssignment->save();
                    }
                } elseif ($activeAssignment && $activeAssignment->employee_id == $oldChiefEmployeeId) {
                    // возможно у нас только активное назначение
                    if (isset($data['old_chief_employee_position_status_id'])) {
                        $activeAssignment->employee_position_status_id = $data['old_chief_employee_position_status_id'];
                        $activeAssignment->save();
                    }
                }
            } else {
                // Оба заданы — возможна замена или смена статуса у того же сотрудника
                if ($oldChiefEmployeeId === $newChiefEmployeeId) {
                    // Тот же сотрудник — просто обновляем его статус (новый)
                    if ($newAssignment) {
                        $newAssignment->employee_position_status_id = $data['employee_position_status_id'] ?? $newAssignment->employee_position_status_id;
                        $newAssignment->save();
                    } elseif ($activeAssignment && $activeAssignment->employee_id == $newChiefEmployeeId) {
                        $activeAssignment->employee_position_status_id = $data['employee_position_status_id'] ?? $activeAssignment->employee_position_status_id;
                        $activeAssignment->save();
                    }
                } else {
                    // Замена: обновляем старого (если есть) и создаём/обновляем нового
                    if ($oldAssignment && isset($data['old_chief_employee_position_status_id'])) {
                        $oldAssignment->employee_position_status_id = $data['old_chief_employee_position_status_id'];
                        $oldAssignment->save();
                    } elseif ($activeAssignment && $activeAssignment->employee_id == $oldChiefEmployeeId && isset($data['old_chief_employee_position_status_id'])) {
                        $activeAssignment->employee_position_status_id = $data['old_chief_employee_position_status_id'];
                        $activeAssignment->save();
                    }

                    if ($newAssignment) {
                        $newAssignment->employee_position_status_id = $data['employee_position_status_id'] ?? $newAssignment->employee_position_status_id;
                        $newAssignment->save();
                    } else {
                        EmployeePosition::create([
                            'employee_id' => $newChiefEmployeeId,
                            'commissariat_position_id' => $chiefSlot->id,
                            'rate' => 1.00,
                            'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                        ]);
                    }
                }
            }

            DB::commit();

            $backUrl = $request->get('backUrl', route('departments.index'));

            return redirect()->to($backUrl)->with('success', '✅ Отдел обновлён');

        } catch (\Exception $e) {
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
