<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Person;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkStatus;
use DB;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index(Request $request)
    {
        // --- Сортировка по ID ---
        $sortOptions = $request->get('sort_id', []); // массив чекбоксов
        if (!is_array($sortOptions)) {
            $sortOptions = [$sortOptions];
        }

        // --- Фильтр по статусам ---
        $selectedStatuses = $request->get('sort_status', []);
        if (!is_array($selectedStatuses)) {
            $selectedStatuses = [$selectedStatuses];
        }

        $sortCommissariats = (array) $request->get('sort_commissariat', []);
        $sortDepartments = (array) $request->get('sort_department', []);
        $sortDivisions = (array) $request->get('sort_division', []);
        $sortPositions = (array) $request->get('sort_position', []);
        $sortTypes = (array) $request->get('sort_type', []);
        $sortRates = (array) $request->get('sort_rate', []);
        $isIndependent = $request->get('is_independent', null);


        $query = Employee::query();

        // --- Фильтр по статусу ---
        if (!empty($selectedStatuses)) {
            $query->whereHas('workStatus', function ($q) use ($selectedStatuses) {
                $q->whereIn('name', $selectedStatuses);
            });
        }

        // --- Сортировка по ID ---
        // Если выбрано несколько, берем первый в массиве (или можно кастомную логику)
        if (!empty($sortOptions)) {
            $query->orderBy('id', $sortOptions[0]); // берем первый выбранный
        } else {
            $query->orderBy('id', 'desc'); // по умолчанию
        }

        $employees = $query->paginate(10)->withQueryString();
        $statuses = WorkStatus::all();
        $positions = Position::all();
        $positionTypes = PositionType::has('positions')->get();

        $commissariats = Commissariat::whereIn('id', function ($query) {
            $query->select('commissariat_id')
                ->from('employee_positions')
                ->distinct()
                ->whereNotNull('employee_id');
        })->get();

        $departments = Department::whereIn('id', function ($query) {
            $query->select('department_id')
                ->from('employee_positions')
                ->distinct()
                ->whereNotNull('employee_id');
        })->get();


        $divisions = Division::whereIn('id', function ($query) {
            $query->select('division_id')
                ->from('employee_positions')
                ->distinct()
                ->whereNotNull('employee_id');
        })->get();


        $column = DB::select("SHOW COLUMNS FROM employee_positions LIKE 'rate'")[0];
        $type = $column->Type;
        preg_match_all("/'([^']+)'/", $type, $matches);
        $rates = $matches[1];


        if (!empty($sortCommissariats)) {
            $query->whereHas('employeePositions', function ($q) use ($sortCommissariats) {
                $q->whereIn('commissariat_id', $sortCommissariats);
            });
        }

        if (!empty($sortDepartments)) {
            $query->whereHas('employeePositions', function ($q) use ($sortDepartments) {
                $q->whereIn('department_id', $sortDepartments);
            });
        }

        if (!empty($sortDivisions)) {
            $query->whereHas('employeePositions', function ($q) use ($sortDivisions) {
                $q->whereIn('division_id', $sortDivisions);
            });
        }


        if (!empty($sortPositions)) {
            $query->whereHas('employeePositions', function ($q) use ($sortPositions) {
                $q->whereIn('position_id', $sortPositions);
            });
        }

        if (!empty($sortTypes)) {
            $query->whereHas('employeePositions', function ($q) use ($sortTypes) {
                $q->whereHas('position', function ($q) use ($sortTypes) {
                    $q->whereIn('position_type_id', $sortTypes);
                });
            });
        }


        if (!empty($sortRates)) {
            $query->whereHas('employeePositions', function ($q) use ($sortRates) {
                $q->whereIn('rate', $sortRates);
            });
        }


        if ($isIndependent === '1') {
            $query->whereHas('employeePositions', function ($q) {
                $q->whereNull('department_id')
                    ->whereNull('division_id');
            });
        }




        return view("admin.employees.index", compact('employees', 'statuses', "positions", 'positionTypes', 'commissariats', 'departments', 'divisions', 'rates'));
    }



    public function create()
    {
        $employees = Employee::with(['user', 'person'])
            ->get();

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->get();

        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->get();

        $roles = Role::all();
        $statuses = WorkStatus::all();

        return view("admin.employees.create")->with([
            "employees" => $employees,
            "users" => $users,
            "persons" => $persons,
            "roles" => $roles,
            "statuses" => $statuses,
        ]);
    }

    public function store(Request $request)
    {
        $requestData = $request->all();
        if (isset($requestData['user_id']) && $requestData['user_id'] === '') {
            $requestData['user_id'] = null;
        }
        if (isset($requestData['person_id']) && $requestData['person_id'] === '') {
            $requestData['person_id'] = null;
        }

        $data = validator($requestData, [
            "work_status" => "required|integer|exists:work_statuses,id",
            "user_id" => "nullable|integer|min:1|exists:users,id",
            "person_id" => "nullable|integer|min:1|exists:persons,id",
        ], [
            'work_status.required' => 'Рабочий статус обязателен',
            'work_status.exists' => 'Выбранный статус работы не существует',
            'user_id.min' => 'ID пользователя должен быть положительным числом',
            'user_id.exists' => 'Выбранный пользователь не существует',
            'person_id.min' => 'ID персональных данных должен быть положительным числом',
            'person_id.exists' => 'Выбранная персона не существует',
        ])->validate();

        $employeeData = [
            'work_status_id' => $data['work_status'],
            'user_id' => $data['user_id'] ?? null,
            'person_id' => $data['person_id'] ?? null,
        ];

        Employee::create($employeeData);

        return redirect()->route("employees.index")->with("success", "Сотрудник создан!");
    }



    public function edit($id)
    {
        $employee = Employee::findOrFail($id);

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->get();

        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->get();

        $roles = Role::all();
        $statuses = WorkStatus::all();

        return view('admin.employees.edit')->with([
            "employee" => $employee,
            "users" => $users,
            "persons" => $persons,
            "roles" => $roles,
            "statuses" => $statuses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $requestData = $request->all();
        if (isset($requestData['user_id']) && $requestData['user_id'] === '') {
            $requestData['user_id'] = null;
        }
        if (isset($requestData['person_id']) && $requestData['person_id'] === '') {
            $requestData['person_id'] = null;
        }

        $data = validator($requestData, [
            "work_status" => "required|integer|exists:work_statuses,id",
            "user_id" => "nullable|integer|min:1|exists:users,id",
            "person_id" => "nullable|integer|min:1|exists:persons,id",
        ], [
            'work_status.required' => 'Рабочий статус обязателен',
            'work_status.exists' => 'Выбранный статус работы не существует',
            'user_id.min' => 'ID пользователя должен быть положительным числом',
            'user_id.exists' => 'Выбранный пользователь не существует',
            'person_id.min' => 'ID персональных данных должен быть положительным числом',
            'person_id.exists' => 'Выбранная персона не существует',
        ])->validate();

        $employeeData = [
            'work_status_id' => $data['work_status'] ?? $employee->work_status_id,
            'user_id' => $data['user_id'] !== null ? $data['user_id'] : $employee->user_id,
            'person_id' => $data['person_id'] !== null ? $data['person_id'] : $employee->person_id,
        ];

        $employee->update($employeeData);

        return redirect()->route("employees.index")->with("success", "Сотрудник обновлен!");
    }

    public function delete($id)
    {
        EmployeePosition::where('employee_id', $id)->delete();
        $res = Employee::where('id', $id)->delete();

        if ($res) {
            return redirect()->route('employees.index')
                ->with('success', 'Сотрудник удален');
        }

        return redirect()->back()
            ->with('error', 'Не удалось удалить сотрудника');
    }
}

