<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Person;
use App\Models\PersonColumn;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkStatus;
use App\Services\JsonColumnService;
use DB;
use Hash;
use Illuminate\Http\Request;

// сервисы

class EmployeesController extends Controller
{
    protected $jsonService;

    // Внедряем сервис через конструктор
    public function __construct(JsonColumnService $jsonService)
    {
        $this->jsonService = $jsonService;
    }

    public function index(Request $request)
    {
        $query = Employee::query();

        // -------------------------
        // Фильтр по статусу
        // -------------------------

        $selectedStatuses = (array) $request->get('sort_status', []);

        if (! empty($selectedStatuses)) {
            $query->whereHas('workStatus', function ($q) use ($selectedStatuses) {
                $q->whereIn('name', $selectedStatuses);
            });
        }

        // -------------------------
        // Фильтры по должностям
        // -------------------------

        $sortCommissariats = (array) $request->get('sort_commissariat', []);
        $sortDepartments = (array) $request->get('sort_department', []);
        $sortDivisions = (array) $request->get('sort_division', []);
        $sortPositions = (array) $request->get('sort_position', []);
        $sortTypes = (array) $request->get('sort_type', []);
        $sortRates = (array) $request->get('sort_rate', []);
        $isIndependent = $request->get('is_independent');

        if (! empty($sortCommissariats)) {
            $query->whereHas(
                'employeePositions',
                fn ($q) => $q->whereIn('commissariat_id', $sortCommissariats)
            );
        }

        if (! empty($sortDepartments)) {
            $query->whereHas(
                'employeePositions',
                fn ($q) => $q->whereIn('department_id', $sortDepartments)
            );
        }

        if (! empty($sortDivisions)) {
            $query->whereHas(
                'employeePositions',
                fn ($q) => $q->whereIn('division_id', $sortDivisions)
            );
        }

        if (! empty($sortPositions)) {
            $query->whereHas(
                'employeePositions',
                fn ($q) => $q->whereIn('position_id', $sortPositions)
            );
        }

        if (! empty($sortTypes)) {
            $query->whereHas(
                'employeePositions.position',
                fn ($q) => $q->whereIn('position_type_id', $sortTypes)
            );
        }

        if (! empty($sortRates)) {
            $query->whereHas(
                'employeePositions',
                fn ($q) => $q->whereIn('rate', $sortRates)
            );
        }

        if ($isIndependent === '1') {
            $query->whereHas('employeePositions', function ($q) {
                $q->where('is_independent', 1);
            });
        }
        // -------------------------
        // Сортировка
        // -------------------------

        $sortOptions = (array) $request->get('sort_id', []);

        if (! empty($sortOptions)) {
            $query->orderBy('id', $sortOptions[0]);
        } else {
            $query->orderBy('id', 'desc');
        }

        // -------------------------
        // paginate В САМОМ КОНЦЕ
        // -------------------------

        $employees = $query->paginate(10)->withQueryString();

        // -------------------------
        // Остальные данные
        // -------------------------

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
        preg_match_all("/'([^']+)'/", $column->Type, $matches);
        $rates = $matches[1];

        return view('admin.employees.index', compact(
            'employees',
            'statuses',
            'positions',
            'positionTypes',
            'commissariats',
            'departments',
            'divisions',
            'rates'
        ));
    }

    // живой поиск
    public function liveSearch(Request $request)
    {
        $query = Employee::query()
            ->with([
                'user.role',
                'person',
                'workStatus',
                'positions.position.positionType',
            ]);

        if ($search = trim($request->search)) {

            $query->where(function ($q) use ($search) {

                // ID сотрудника
                $q->where('id', 'like', "%{$search}%")

                    // Логин
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('login', 'like', "%{$search}%");
                    })

                    // ФИО
                    ->orWhereHas('person', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('patronymic', 'like', "%{$search}%");
                    })

                    // 🔥 Поиск по телефонам (JSON LIKE)
                    ->orWhereHas('person', function ($q) use ($search) {
                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(phones, '$')) LIKE ?", ["%{$search}%"]);
                    })

                    // 🔥 Поиск по email (JSON LIKE)
                    ->orWhereHas('person', function ($q) use ($search) {
                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(emails, '$')) LIKE ?", ["%{$search}%"]);
                    });

            });
        }

        $employees = $query->limit(50)->get();

        return view('admin.employees.partials.table-body', compact('employees'))->render();
    }

    public function create(Request $request)
    {

        $usedUserIds = Employee::pluck('user_id')->filter()->toArray();
        $users = User::whereNotIn('id', $usedUserIds)
            ->get();
        $usedPersonIds = Employee::pluck('person_id')->filter()->toArray();
        $persons = Person::whereNotIn('id', $usedPersonIds)
            ->get();
        $roles = Role::all();
        $statuses = WorkStatus::all();

        $backUrl = $request->input('back_url');

        $columns = PersonColumn::getTableColumns();

        return view('admin.employees.create')->with([
            'users' => $users,
            'persons' => $persons,
            'roles' => $roles,
            'statuses' => $statuses,
            'backUrl' => $backUrl,
            'columns' => $columns,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'work_status' => 'required|integer|exists:work_statuses,id',
            'last_name' => 'required|string|min:2',
            'first_name' => 'required|string|min:2',
            'patronymic' => 'nullable|string|min:2',

            'login' => [
                'required',
                'min:5',
                'max:255',
                'unique:users',
            ],
            'password' => [
                'required',
                'min:5',
                'max:255',
            ],
            'role' => 'required|exists:roles,id',
        ], [
            'work_status.required' => 'Рабочий статус обязателен',
            'work_status.exists' => 'Выбранный статус работы не существует',

            'last_name.required' => 'Поле Фамилия обязательно для заполнения',
            'last_name.min' => 'Поле Фамилия минимум 2 символа',
            'first_name.required' => 'Поле Имя обязательно для заполнения',
            'first_name.min' => 'Поле Имя минимум 2 символа',
            'patronymic.min' => 'Поле Отчество минимум 2 символа',

            'login.required' => 'Логин обязателен',
            'login.min' => 'Логин минимум 5 символов',
            'login.max' => 'Логин максимум 255 символов',
            'login.unique' => 'Логин уже занят',
            'password.required' => 'Пароль обязателен',
            'password.min' => 'Пароль минимум 5 символов',
            'password.max' => 'Пароль максимум 255 символов',
            'role.required' => 'Роль обязательна',
            'role.exists' => 'Недопустимое значение для роли',
        ]);

        $columns = PersonColumn::getTableColumns();

        $personData = [];

        foreach ($columns as $column) {
            $name = $column['name'];
            $type = $column['type'];
            $value = $request->input($name);

            // исключаем системные поля
            if (in_array($name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            // Получаем индексы удаленных файлов
            $removedIndexes = $request->input("removed_{$name}_indexes", []);

            // Обрабатываем только те файлы, которые не были удалены
            $uploadedFiles = [];
            if ($request->hasFile($name)) {
                foreach ($request->file($name) as $index => $file) {
                    if (! in_array($index, $removedIndexes) && $file->isValid()) {
                        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                        $path = $file->storeAs("uploads/{$name}", $filename, 'public');
                        $uploadedFiles[] = $path;
                    }
                }

                $personData[$name]=$uploadedFiles;
            }

            // if (str_contains($type, 'varchar')) {
            //     if ($request->hasFile($name)) {
            //         $file = $request->file($name);

            //         $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            //         $path = $file->storeAs("uploads/{$name}", $filename, 'public');

            //         $personData[$name] = $path;
            //     } else {
            //         $personData[$name] = null;
            //     }

            //     continue;
            // }

            if (
                str_contains($type, 'longtext')
            ) {

                if ($value === null || trim((string) $value) === '') {
                    $personData[$name] = null;
                } else {
                    $personData[$name] = json_encode(
                        $this->jsonService->parseLines($value),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }

                continue;
            }

            $nullable = $column['nullable'];
            $default = $column['default'];

            if ($value === null || $value === '') {

                // если можно NULL → ставим NULL
                if ($nullable) {
                    $personData[$name] = null;
                }

                // если есть DEFAULT → не передаём поле вообще (MySQL сам подставит)
                elseif ($default !== null) {
                    continue;
                }

                // если NOT NULL и нет DEFAULT → ставим безопасное значение
                else {
                    if (str_contains($type, 'int')) {
                        $personData[$name] = 0;
                    } elseif (str_contains($type, 'decimal')) {
                        $personData[$name] = 0;
                    } elseif (str_contains($type, 'date')) {
                        $personData[$name] = now();
                    } else {
                        $personData[$name] = '';
                    }
                }

                continue;
            } elseif (str_contains($type, 'int')) {
                $personData[$name] = (int) $value;
            } elseif (str_contains($type, 'decimal')) {
                $personData[$name] = (float) $value;
            } elseif (str_contains($type, 'date')) {
                $personData[$name] = $value;
            } else {
                $personData[$name] = $value;
            }
        }

        $person = Person::create($personData);

        $userData = [
            'login' => $data['login'],
            'role_id' => $data['role'],
        ];
        $userData['password_hash'] = Hash::make($data['password']);
        $user = User::create($userData);

        $employee = Employee::create();
        $employee->work_status_id = $data['work_status'];
        $employee->user_id = $user->id;
        $employee->person_id = $person->id;
        $employee->save();

        $backUrl = $request->get('backUrl', route('employees.index'));

        return redirect()->to($backUrl)
            ->with('success', 'Сотрудник успешно создан!');
    }

    public function edit(Request $request, $id)
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

        $backUrl = $request->input('back_url');

        $columns = PersonColumn::getTableColumns();

        return view('admin.employees.edit')->with([
            'employee' => $employee,
            'users' => $users,
            'persons' => $persons,
            'roles' => $roles,
            'statuses' => $statuses,
            'backUrl' => $backUrl,
            'columns' => $columns,
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with(['person', 'user'])->findOrFail($id);

        $data = $request->validate([
            'work_status' => 'required|integer|exists:work_statuses,id',
            'last_name' => 'required|string|min:2',
            'first_name' => 'required|string|min:2',
            'patronymic' => 'nullable|string|min:2',

            'login' => [
                'required',
                'min:5',
                'max:255',
                'unique:users',
            ],
            'password' => [
                'required',
                'min:5',
                'max:255',
            ],
            'role' => 'required|exists:roles,id',
        ], [
            'work_status.required' => 'Рабочий статус обязателен',
            'work_status.exists' => 'Выбранный статус работы не существует',

            'last_name.required' => 'Поле Фамилия обязательно для заполнения',
            'last_name.min' => 'Поле Фамилия минимум 2 символа',
            'first_name.required' => 'Поле Имя обязательно для заполнения',
            'first_name.min' => 'Поле Имя минимум 2 символа',
            'patronymic.required' => 'Поле Отчество обязательно для заполнения',
            'patronymic.min' => 'Поле Отчество минимум 2 символа',

            'login.required' => 'Логин обязателен',
            'login.min' => 'Логин минимум 5 символов',
            'login.max' => 'Логин максимум 255 символов',
            'login.unique' => 'Логин уже занят',
            'password.required' => 'Пароль обязателен',
            'password.min' => 'Пароль минимум 5 символов',
            'password.max' => 'Пароль максимум 255 символов',
            'role.required' => 'Роль обязательна',
            'role.exists' => 'Недопустимое значение для роли',
        ]);

        // person
        $person = $employee->person;

        $columns = PersonColumn::getTableColumns();

        $personData = [];

        foreach ($columns as $column) {
            $name = $column['name'];
            $type = $column['type'];

            // исключаем системные поля
            if (in_array($name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            // FILE (BLOB) — обрабатываем отдельно

            // FILE (BLOB) — обрабатываем отдельно
            if (str_contains($type, 'blob')) {
                if ($request->hasFile($name)) {
                    // 1. Если загружен новый файл
                    $file = $request->file($name);

                    // Читаем новый файл
                    $personData[$name] = file_get_contents($file->getRealPath());

                } else {
                    // 2. Если новый файл НЕ загружен — оставляем старый
                    // НЕ передаем поле в $personData, чтобы Laravel не перезаписал его NULL
                    // Просто пропускаем — continue без сохранения
                    continue;
                }

                // ВАЖНО: continue должен быть ПОСЛЕ обработки обоих случаев
                continue;
            }
            $value = $request->input($name);

            // 🔴 сначала JSON
            if (
                str_contains($type, 'json') ||
                (str_contains($type, 'longtext') && preg_match('/^a\d+$/', $name))
            ) {

                if ($value === null || trim((string) $value) === '') {
                    $personData[$name] = null;
                } else {
                    $personData[$name] = json_encode(
                        $this->jsonService->parseLines($value),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }

                continue;
            }

            // пустые значения → NULL
            $nullable = $column['nullable'];
            $default = $column['default'];

            if ($value === null || $value === '') {

                // если можно NULL → ставим NULL
                if ($nullable) {
                    $personData[$name] = null;
                }

                // если есть DEFAULT → не передаём поле вообще (MySQL сам подставит)
                elseif ($default !== null) {
                    continue;
                }

                // если NOT NULL и нет DEFAULT → ставим безопасное значение
                else {
                    if (str_contains($type, 'int')) {
                        $personData[$name] = 0;
                    } elseif (str_contains($type, 'decimal') || str_contains($type, 'float')) {
                        $personData[$name] = 0;
                    } elseif (str_contains($type, 'date') || str_contains($type, 'time')) {
                        $personData[$name] = now(); // или null если хочешь падать
                    } else {
                        $personData[$name] = ''; // varchar/text
                    }
                }

                continue;
            }

            // INT
            elseif (str_contains($type, 'int')) {
                $personData[$name] = (int) $value;
            }

            // DECIMAL / FLOAT
            elseif (str_contains($type, 'decimal') || str_contains($type, 'float')) {
                $personData[$name] = (float) $value;
            }

            // DATE / DATETIME
            elseif (str_contains($type, 'date') || str_contains($type, 'time')) {
                $personData[$name] = $value; // при необходимости можно нормализовать
            }

            // ВСЁ ОСТАЛЬНОЕ (varchar, text)
            else {
                $personData[$name] = $value;
            }
        }

        if ($person) {
            $person->update($personData);
        } else {
            $person = Person::create($personData);
            $employee->person_id = $person->id;
            $employee->save();
        }

        // user
        $userData = [
            'login' => $data['login'],
            'role_id' => $data['role'],
        ];
        if (! empty($data['password'])) {
            $userData['password_hash'] = Hash::make($data['password']);
        }

        if ($employee->user) {
            $employee->user->update($userData);
            $user = $employee->user;
        } else {
            $user = User::create($userData);
            $employee->user_id = $user->id;
        }

        // workStatus
        $employee->work_status_id = $data['work_status'];
        $employee->save();

        $backUrl = $request->get('backUrl', route('employees.index'));

        return redirect()->to($backUrl)
            ->with('success', 'Сотрудник успешно обновлен!');

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
