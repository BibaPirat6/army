<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePositionRate;
use App\Models\EmployeePositionStatus;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeePositionsController extends Controller
{
    public function create(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $positions = Position::all();
        $commissariats = Commissariat::all();
        $departments = Department::all();
        $divisions = Division::all();
        $employeePositionRates = EmployeePositionRate::all();

        $backUrl = $request->get('backUrl');
        $employeeId = $id ?? $request->get('employeeId');
        $commissariatId = $request->get('commissariatId');
        $departmentId = $request->get('departmentId');
        $divisionId = $request->get('divisionId');

        // Загружаем данные для отображения
        $commissariat = null;
        $department = null;
        $division = null;

        if ($commissariatId) {
            $commissariat = Commissariat::find($commissariatId);
        }
        if ($departmentId) {
            $department = Department::find($departmentId);
        }
        if ($divisionId) {
            $division = Division::find($divisionId);
        }

        // Передаем все переменные явно, а не через compact
        return view('admin.org.employee-positions.create', [
            'employee' => $employee,
            'positions' => $positions,
            'commissariats' => $commissariats,
            'departments' => $departments,
            'divisions' => $divisions,
            'backUrl' => $backUrl,
            'employeeId' => $employeeId,
            'employeePositionRates' => $employeePositionRates,
            'commissariatId' => $commissariatId,
            'departmentId' => $departmentId,
            'divisionId' => $divisionId,
            'commissariat' => $commissariat,
            'department' => $department,
            'division' => $division,
        ]);
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'rate' => 'required|numeric',

            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'department_id' => [
                'nullable',
                'sometimes',
                Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],
            'division_id' => [
                'nullable',
                'sometimes',
                Rule::exists('divisions', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],

            'is_independent' => 'required|integer|in:1,0',
            'employeeId' => 'nullable|integer|min:1|exists:employees,id',
        ], [
            'position_id.required' => 'Поле должность обязательно для заполнения.',
            'position_id.integer' => 'Поле должность должно быть целым числом.',
            'position_id.exists' => 'Выбранная должность не существует.',
            'rate.required' => 'Поле ставка обязательно для заполнения.',
            'rate.numeric' => 'Поле ставка должно быть числом.',
            'rate.min' => 'Минимальное значение ставки 0.25.',
            'rate.max' => 'Максимальное значение ставки 2.0.',
            'commissariat_id.required' => 'Выберите комиссариат',
            'commissariat_id.exists' => 'Несуществующий комиссариат',
            'department_id.exists' => 'Несуществующий отдел',
            'division_id.exists' => 'Несуществующий отдел',
            'is_independent.required' => 'Выберите тип должность самостоятельная/нет',
            'employeeId.exists' => 'Не существующий id сотрудника',
        ]);

        EmployeePosition::create([
            'employee_id' => $id,
            'commissariat_id' => $data['commissariat_id'],
            'department_id' => $data['department_id'],
            'division_id' => $data['division_id'],
            'position_id' => $data['position_id'],
            'employee_position_rate_id' => $data['rate'],
            'is_independent' => $data['is_independent'],
        ]);

        $backUrl = $request->get('backUrl');

        return redirect()->to($backUrl)->with('success', 'Должность успешно добавлена сотруднику.');
    }

    public function edit(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $positions = Position::all();
        $commissariats = Commissariat::all();
        $departments = Department::all();
        $divisions = Division::all();
        $employeePositionStatuses = EmployeePositionStatus::all();

        $backUrl = $request->get('back_url');
        $employeeId = $id;

        return view('admin.org.employee-positions.edit', compact('employee', 'positions', 'commissariats', 'departments', 'divisions', 'backUrl', 'employeeId', 'employeePositionStatuses'));
    }

    public function update(Request $request, $id)
    {
        // Извлекаем position_id из массива positions
        $positionsData = $request->input('positions', []);

        // Получаем первый (или единственный) position_id из массива
        $positionId = null;
        if (! empty($positionsData)) {
            $firstPosition = reset($positionsData);
            $positionId = $firstPosition['position_id'] ?? null;
        }

        // Добавляем position_id в запрос для валидации
        $request->merge(['position_id' => $positionId]);

        $data = $request->validate([
            'position_id' => 'required|integer|exists:positions,id',
            'rate' => 'required|numeric',
            'status' => 'required',
            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'department_id' => [
                'nullable',
                'sometimes',
                Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],
            'division_id' => [
                'nullable',
                'sometimes',
                Rule::exists('divisions', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],
            'is_independent' => 'required|in:0,1',
        ], [
            'position_id.required' => 'Поле должность обязательно для заполнения.',
            'position_id.integer' => 'Поле должность должно быть целым числом.',
            'position_id.exists' => 'Выбранная должность не существует.',
            'rate.required' => 'Поле ставка обязательно для заполнения.',
            'rate.numeric' => 'Поле ставка должно быть числом.',
            'commissariat_id.required' => 'Обязательное поле комиссариата',
            'commissariat_id.exists' => 'Несуществующий комиссариат',
            'department_id.exists' => 'Несуществующий отдел',
            'division_id.exists' => 'Несуществующее отделение',
            'is_independent.required' => 'Обязательное поле выбора самостоятельной должности',
        ]);

        $employeePosition = EmployeePosition::findOrFail($id);

        $employeePosition->update([
            'position_id' => $data['position_id'],
            'commissariat_id' => $data['commissariat_id'],
            'department_id' => $data['department_id'],
            'division_id' => $data['division_id'],
            'is_independent' => $data['is_independent'],
            'employee_position_rate_id' => $data['rate'],
            'employee_position_status_id' => $data['status'],
        ]);

        $backUrl = $request->input('backUrl');

        return redirect($backUrl)->with('success', 'Должность успешно обновлена.');
    }

    public function delete(Request $request, $id)
    {
        $employeePosition = EmployeePosition::findOrFail($id);
        $employeePosition->delete();

        $backUrl = $request->get('backUrl');

        return redirect()->to($backUrl)->with('success', 'Должность успешно удалена у сотрудника.');
    }

    public function add(Request $request)
    {
        $employees = Employee::all();
        $positions = Position::all();
        $commissariats = Commissariat::all();
        $departments = Department::all();
        $divisions = Division::all();
        $employeePositionRates = EmployeePositionRate::all();

        $backUrl = $request->get('back_url');
        $commissariatId = $request->get('commissariat_id');
        $departmentId = $request->get('department_id');
        $divisionId = $request->get('division_id');

        // Загружаем данные для отображения
        $commissariat = null;
        $department = null;
        $division = null;

        if ($commissariatId) {
            $commissariat = Commissariat::find($commissariatId);
        }
        if ($departmentId) {
            $department = Department::find($departmentId);
        }
        if ($divisionId) {
            $division = Division::find($divisionId);
        }

        // Передаем все переменные явно, а не через compact
        return view('admin.org.employee-positions.add', [
            'positions' => $positions,
            'commissariats' => $commissariats,
            'departments' => $departments,
            'divisions' => $divisions,
            'backUrl' => $backUrl,
            'employeePositionRates' => $employeePositionRates,
            'commissariatId' => $commissariatId,
            'departmentId' => $departmentId,
            'divisionId' => $divisionId,
            'commissariat' => $commissariat,
            'department' => $department,
            'division' => $division,
            'employees' => $employees,
        ]);
    }

    public function addStore(Request $request)
    {
        $data = $request->validate([
            'chief_employee_id' => 'required|exists:employees,id',
            'position_id' => 'required|integer|exists:positions,id',
            'rate' => 'required|numeric',

            'commissariat_id' => 'required|integer|min:1|exists:commissariats,id',
            'department_id' => [
                'nullable',
                'sometimes',
                Rule::exists('departments', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],
            'division_id' => [
                'nullable',
                'sometimes',
                Rule::exists('divisions', 'id')->where(function ($query) use ($request) {
                    return $query->where('commissariat_id', $request->commissariat_id);
                }),
            ],

            'is_independent' => 'required|integer|in:1,0',
        ], [
            'chief_employee_id.required' => 'Выберите сотрудника',
            'position_id.required' => 'Поле должность обязательно для заполнения.',
            'position_id.integer' => 'Поле должность должно быть целым числом.',
            'position_id.exists' => 'Выбранная должность не существует.',
            'rate.required' => 'Поле ставка обязательно для заполнения.',
            'rate.numeric' => 'Поле ставка должно быть числом.',
            'rate.min' => 'Минимальное значение ставки 0.25.',
            'rate.max' => 'Максимальное значение ставки 2.0.',
            'commissariat_id.required' => 'Выберите комиссариат',
            'commissariat_id.exists' => 'Несуществующий комиссариат',
            'department_id.exists' => 'Несуществующий отдел',
            'division_id.exists' => 'Несуществующий отдел',
            'is_independent.required' => 'Выберите тип должность самостоятельная/нет',
        ]);

        EmployeePosition::create([
            'employee_id' => $data['chief_employee_id'],
            'commissariat_id' => $data['commissariat_id'],
            'department_id' => $data['department_id'],
            'division_id' => $data['division_id'],
            'position_id' => $data['position_id'],
            'employee_position_rate_id' => $data['rate'],
            'is_independent' => $data['is_independent'],
        ]);

        $backUrl = $request->get('backUrl');

        return redirect()->to($backUrl)->with('success', 'Должность успешно добавлена сотруднику.');
    }
}
