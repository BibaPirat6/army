<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeePosition;
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

        $positions = Position::whereHas('chiefType', function ($query) {
            $query->where('name', 'начальник отдела');
        })->get();

        $backUrl = $request->get('back_url');

        return view('admin.org.departments.create', compact('commissariats', 'employees', 'commissariat', 'backUrl', 'positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'chief_employee_id' => 'required|sometimes|integer|min:1|exists:employees,id',
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

        $department = Department::create([
            'name' => $data['name'],
            'commissariat_id' => $data['commissariat_id'],
        ]);
        $department->refresh();

        EmployeePosition::create(
            [
                'employee_id' => $data['chief_employee_id'],
                'position_id' => $data['chief_position_id'],
                'commissariat_id' => $data['commissariat_id'],
                'department_id' => $department->id,
            ]
        );

        $backUrl = $request->get('backUrl', route('departments.index'));

        return redirect()->to($backUrl)->with('success', 'Отдел успешно создан.');
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

        $backUrl = $request->input('back_url');

        return view('admin.org.departments.edit', compact('department', 'commissariats', 'employees', 'backUrl', 'positions'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'chief_employee_id' => 'required|integer|exists:employees,id',
            'chief_position_id' => 'required|integer|exists:positions,id',
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

        DB::transaction(function () use ($department, $data) {
            $department->update([
                'name' => $data['name'],
                'commissariat_id' => $data['commissariat_id'],
            ]);

            $currentAssignment = EmployeePosition::where('department_id', $department->id)
                ->whereHas('position.chiefType', function ($q) {
                    $q->where('name', 'начальник отдела');
                })->first();

            // Если выбран новый начальник (id) — обрабатываем создание/смену
            if (! empty($data['chief_employee_id'])) {
                $newEmployeeId = $data['chief_employee_id'];
                $newPositionId = $data['chief_position_id'];

                if ($currentAssignment) {
                    // если выбран другой сотрудник — удалить старую запись и создать новую
                    if ($currentAssignment->employee_id != $newEmployeeId) {
                        $currentAssignment->delete();

                        EmployeePosition::create([
                            'employee_id' => $newEmployeeId,
                            'position_id' => $newPositionId,
                            'commissariat_id' => $data['commissariat_id'],
                            'department_id' => $department->id,
                            'rate' => $currentAssignment->rate ?? 1,
                        ]);
                    } else {
                        // тот же сотрудник — если поменялась позиция или commissariat — обновляем запись
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
                        if ($needUpdate) {
                            $currentAssignment->update($updateData);
                        }
                    }
                } else {
                    // не было текущего — создаём новую запись
                    EmployeePosition::create([
                        'employee_id' => $newEmployeeId,
                        'position_id' => $newPositionId,
                        'commissariat_id' => $data['commissariat_id'],
                        'department_id' => $department->id,
                    ]);
                }
            } else {
                // если начальник снят (null) — удаляем существующее назначение
                if ($currentAssignment) {
                    $currentAssignment->delete();
                }
            }
        });

        $backUrl = $request->get('backUrl', route('departments.index'));

        return redirect()->to($backUrl)->with('success', 'Отдел успешно обновлен.');
    }

    public function delete($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Отдел успешно удален.');
    }
}
