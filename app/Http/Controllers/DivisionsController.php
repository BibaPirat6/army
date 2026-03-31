<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Position;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisionsController extends Controller
{
    public function index()
    {
        $divisions = Division::paginate(50);

        return view('admin.org.divisions.index', compact('divisions'));
    }

    public function show(Request $request, $id)
    {
        $division = Division::findOrFail($id);
        $backUrl = $request->input('back_url');

        return view('admin.org.divisions.show', compact('division', 'backUrl'));
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
        ], [
            'name.required' => 'Название отделения обязательно для заполнения.',
            'name.string' => 'Название отделения должно быть строкой.',
            'name.min' => 'Название отделения должно содержать минимум 2 символа.',
            'name.max' => 'Название отделения не должно превышать 255 символов.',
            'commissariat_id.required' => 'Выберите комиссариат',
            'commissariat_id.exists' => 'Несуществующий комиссариат',
            'department_id.exists' => 'Несуществующий отдел',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
            'chief_position_id.exists' => 'Несуществующая должность',
        ]);

        $division = Division::create([
            'name' => $data['name'],
            'commissariat_id' => $data['commissariat_id'],
            'department_id' => $data['department_id'] ?? null,
        ]);
        $division->refresh();

        EmployeePosition::create(
            [
                'employee_id' => $data['chief_employee_id'],
                'position_id' => $data['chief_position_id'],
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
                'division_id' => $division->id,
            ]
        );

        $backUrl = $request->get('backUrl', route('divisions.index'));

        return redirect()->to($backUrl)->with('success', 'Отделение успешно создано.');
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

        $backUrl = $request->input('back_url');

        return view('admin.org.divisions.edit', compact('division', 'commissariats', 'departments', 'employees', 'backUrl', 'positions'));
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
        ], [
            'name.required' => 'Название отделения обязательно для заполнения.',
            'name.string' => 'Название отделения должно быть строкой.',
            'name.min' => 'Название отделения должно содержать минимум 2 символа.',
            'name.max' => 'Название отделения не должно превышать 255 символов.',
            'commissariat_id.required' => 'Выберите комиссариат',
            'commissariat_id.exists' => 'Несуществующий комиссариат',
            'department_id.exists' => 'Несуществующий отдел',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
            'chief_position_id.exists' => 'Несуществующая должность',
        ]);

        $division = Division::findOrFail($id);

        DB::transaction(function () use ($division, $data) {
            $division->update([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $data['department_id'] ?? null,
            ]);

            // Поиск текущей записи начальника отделения
            $currentAssignment = EmployeePosition::where('division_id', $division->id)
                ->whereHas('position.chiefType', function ($q) {
                    $q->where('name', 'начальник отделения');
                })->first();

            // Если выбран новый начальник — обрабатываем создание/смену
            if (! empty($data['chief_employee_id']) && ! empty($data['chief_position_id'])) {
                $newEmployeeId = $data['chief_employee_id'];
                $newPositionId = $data['chief_position_id'];

                if ($currentAssignment) {
                    // Если выбран другой сотрудник — удаляем старую запись и создаём новую
                    if ($currentAssignment->employee_id != $newEmployeeId) {
                        $currentAssignment->delete();

                        EmployeePosition::create([
                            'employee_id' => $newEmployeeId,
                            'position_id' => $newPositionId,
                            'commissariat_id' => $data['commissariat_id'],
                            'department_id' => $data['department_id'] ?? null,
                            'division_id' => $division->id,
                            'rate' => $currentAssignment->rate ?? 1,
                        ]);
                    } else {
                        // Тот же сотрудник — если поменялись данные, обновляем запись
                        $needUpdate = false;
                        $updateData = [];

                        if ($currentAssignment->position_id != $newPositionId) {
                            $updateData['position_id'] = $newPositionId;
                            $needUpdate = true;
                        }
                        if ($currentAssignment->commissariat_id != $data['commissariat_id']) {
                            $updateData['commissariat_id'] = $data['commissariat_id'];
                            $needUpdate = true;
                        }
                        // Проверка department_id с учётом null
                        $currentDeptId = $currentAssignment->department_id;
                        $newDeptId = $data['department_id'] ?? null;
                        if ($currentDeptId != $newDeptId) {
                            $updateData['department_id'] = $newDeptId;
                            $needUpdate = true;
                        }

                        if ($needUpdate) {
                            $currentAssignment->update($updateData);
                        }
                    }
                } else {
                    // Не было текущего назначения — создаём новую запись
                    EmployeePosition::create([
                        'employee_id' => $newEmployeeId,
                        'position_id' => $newPositionId,
                        'commissariat_id' => $data['commissariat_id'],
                        'department_id' => $data['department_id'] ?? null,
                        'division_id' => $division->id,
                    ]);
                }
            } else {
                // Если начальник снят — удаляем существующее назначение
                if ($currentAssignment) {
                    $currentAssignment->delete();
                }
            }
        });

        $backUrl = $request->get('backUrl', route('divisions.index'));

        return redirect()->to($backUrl)->with('success', 'Отделение успешно обновлено.');
    }

    public function delete($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();

        return redirect()->route('divisions.index')->with('success', 'Подразделение успешно удалено.');
    }
}
