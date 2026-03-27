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
use App\Services\JsonColumnService;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        // NOTE: employee_positions не содержит commissariat_id/department_id/division_id напрямую.
        // Эти поля находятся в таблице commissariats_positions, EmployeePosition имеет commissariat_position_id.
        if (! empty($sortCommissariats)) {
            $query->whereHas('employeePositions', function ($q) use ($sortCommissariats) {
                $q->whereHas('commissariatPosition', function ($q2) use ($sortCommissariats) {
                    $q2->whereIn('commissariat_id', $sortCommissariats);
                });
            });
        }

        if (! empty($sortDepartments)) {
            $query->whereHas('employeePositions', function ($q) use ($sortDepartments) {
                $q->whereHas('commissariatPosition', function ($q2) use ($sortDepartments) {
                    $q2->whereIn('department_id', $sortDepartments);
                });
            });
        }

        if (! empty($sortDivisions)) {
            $query->whereHas('employeePositions', function ($q) use ($sortDivisions) {
                $q->whereHas('commissariatPosition', function ($q2) use ($sortDivisions) {
                    $q2->whereIn('division_id', $sortDivisions);
                });
            });
        }

        if (! empty($sortPositions)) {
            $query->whereHas('employeePositions', function ($q) use ($sortPositions) {
                $q->whereHas('commissariatPosition', function ($q2) use ($sortPositions) {
                    $q2->whereIn('position_id', $sortPositions);
                });
            });
        }

        if (! empty($sortTypes)) {
            $query->whereHas('employeePositions', function ($q) use ($sortTypes) {
                $q->whereHas('commissariatPosition.position', function ($q2) use ($sortTypes) {
                    $q2->whereIn('position_type_id', $sortTypes);
                });
            });
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

        // Получаем commissariats / departments / divisions через commissariats_positions JOIN employee_positions,
        // потому что именно там хранятся соответствующие поля.
        $commissariats = Commissariat::whereIn('id', function ($query) {
            $query->select('commissariats_positions.commissariat_id')
                ->from('commissariats_positions')
                ->join('employee_positions', 'commissariats_positions.id', '=', 'employee_positions.commissariat_position_id')
                ->whereNotNull('employee_positions.employee_id')
                ->distinct();
        })->get();

        $departments = Department::whereIn('id', function ($query) {
            $query->select('commissariats_positions.department_id')
                ->from('commissariats_positions')
                ->join('employee_positions', 'commissariats_positions.id', '=', 'employee_positions.commissariat_position_id')
                ->whereNotNull('employee_positions.employee_id')
                ->distinct();
        })->get();

        $divisions = Division::whereIn('id', function ($query) {
            $query->select('commissariats_positions.division_id')
                ->from('commissariats_positions')
                ->join('employee_positions', 'commissariats_positions.id', '=', 'employee_positions.commissariat_position_id')
                ->whereNotNull('employee_positions.employee_id')
                ->distinct();
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

        $backUrl = $request->input('back_url');

        $columns = Person::getTableColumns();

        return view('admin.employees.create')->with([
            'users' => $users,
            'persons' => $persons,
            'roles' => $roles,
            'backUrl' => $backUrl,
            'columns' => $columns,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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

        $columns = Person::getTableColumns();

        $personData = [];

        foreach ($columns as $column) {
            $name = $column['name'];
            $type = $column['type'];
            $value = $request->input($name);
            $comment = $column['comment'] ?? null;

            // исключаем системные поля
            if (in_array($name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            if (str_contains($type, 'longtext') && in_array($comment, ['file', 'multiple', 'single'])) {
                // Используем сервис для обработки файлов
                $uploadedFiles = $this->jsonService->handleFiles(
                    $request->file($name),
                    $name
                );

                // Сохраняем как JSON массив
                $personData[$name] = $uploadedFiles !== null
                    ? json_encode($uploadedFiles, JSON_UNESCAPED_SLASHES)
                    : null;

                continue;
            }

            // 👇 Обработка JSON списка (по комментарию 'json')
            if (str_contains($type, 'longtext') && $comment === 'json') {
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

        $columns = Person::getTableColumns();

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

            // логин уникален, но игнорируем текущего пользователя
            'login' => [
                'required',
                'min:5',
                'max:255',
                'unique:users,login,' . ($employee->user->id ?? 'NULL'),
            ],
            // пароль теперь необязательный при редактировании
            'password' => [
                'nullable',
                'min:5',
                'max:255',
            ],
            'role' => 'required|exists:roles,id',
        ], [
            'work_status.required' => 'Рабочий статус обязателен',
            'work_status.exists' => 'Выбранный статус работы не существует',

            'login.required' => 'Логин обязателен',
            'login.min' => 'Логин минимум 5 символов',
            'login.max' => 'Логин максимум 255 символов',
            'login.unique' => 'Логин уже занят',
            'password.min' => 'Пароль минимум 5 символов',
            'password.max' => 'Пароль максимум 255 символов',
            'role.required' => 'Роль обязательна',
            'role.exists' => 'Недопустимое значение для роли',
        ]);

        // person
        $person = $employee->person;

        $columns = Person::getTableColumns();

        $personData = [];

        foreach ($columns as $column) {
            $name = $column['name'];
            $type = $column['type'];
            $value = $request->input($name);
            $comment = $column['comment'] ?? null;

            // исключаем системные поля
            if (in_array($name, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            // ========== файлы (редактирование) ==========
            if (str_contains($type, 'longtext') && in_array($comment, ['file', 'multiple', 'single'])) {

                // индексы/пути отмеченных для удаления (формат из шаблона)
                $removedIndexes = (array) $request->input("removed_{$name}_indexes", []);
                $removedPaths = (array) $request->input("removed_{$name}_existing_paths", []);

                // текущие файлы из БД
                $existingFiles = [];
                if ($person && $person->$name) {
                    $existingFiles = json_decode($person->$name, true) ?? [];
                }

                // сначала обрабатываем удаление отмеченных существующих файлов
                if (!empty($removedIndexes)) {
                    // удаляем по индексам (если индекс существует) и по пути (если передан)
                    foreach ($removedIndexes as $idx) {
                        if (isset($existingFiles[$idx])) {
                            // попытка удалить физически (без фатальной ошибки)
                            try {
                                if (!empty($existingFiles[$idx])) {
                                    Storage::disk('public')->delete($existingFiles[$idx]);
                                }
                            } catch (\Throwable $e) {
                                // silent — не ломаем обновление из-за удаления файла
                            }
                            unset($existingFiles[$idx]);
                        }
                    }
                    // если также передали пути — пробуем удалить их (дополнительно)
                    foreach ($removedPaths as $p) {
                        try {
                            if (!empty($p)) Storage::disk('public')->delete($p);
                        } catch (\Throwable $e) {
                        }
                    }

                    // переиндексируем массив существующих файлов
                    $existingFiles = array_values($existingFiles);
                }

                // обработка новых загруженных файлов (если есть поле в запросе)
                $newFiles = $this->jsonService->handleFiles(
                    $request->file($name),
                    $name
                );

                // Если поле с файлами вообще отсутствует в запросе -> newFiles === null (ничего менять)
                // Если newFiles === null и не было removals → ничего не меняем (оставляем старое значение)
                if ($newFiles === null && empty($removedIndexes)) {
                    continue;
                }

                // Нормализуем newFiles к массиву (если null -> пустой массив)
                $newFiles = $newFiles === null ? [] : (is_array($newFiles) ? $newFiles : []);

                // объединяем оставшиеся существующие и новые
                $finalFiles = array_merge($existingFiles, $newFiles);

                if (!empty($finalFiles)) {
                    $personData[$name] = json_encode($finalFiles, JSON_UNESCAPED_SLASHES);
                } else {
                    // если итог пустой — смотрим nullable колонки
                    $personData[$name] = $column['nullable'] ? null : json_encode([], JSON_UNESCAPED_SLASHES);
                }

                continue;
            }

            // ========== json-списки ==========
            if (str_contains($type, 'longtext') && $comment === 'json') {

                if ($value === null || trim((string) $value) === '') {
                    $personData[$name] = $column['nullable'] ? null : json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $personData[$name] = json_encode(
                        $this->jsonService->parseLines($value),
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                }

                continue;
            }

            // обычные поля — стандартная логика
            $nullable = $column['nullable'];
            $default = $column['default'];

            if ($value === null || $value === '') {

                if ($nullable) {
                    $personData[$name] = null;
                } elseif ($default !== null) {
                    continue;
                } else {
                    if (str_contains($type, 'int')) {
                        $personData[$name] = 0;
                    } elseif (str_contains($type, 'decimal') || str_contains($type, 'float')) {
                        $personData[$name] = 0;
                    } elseif (str_contains($type, 'date') || str_contains($type, 'time')) {
                        $personData[$name] = now();
                    } else {
                        $personData[$name] = '';
                    }
                }

                continue;
            }

            // типизация
            if (str_contains($type, 'int')) {
                $personData[$name] = (int) $value;
            } elseif (str_contains($type, 'decimal') || str_contains($type, 'float')) {
                $personData[$name] = (float) $value;
            } elseif (str_contains($type, 'date') || str_contains($type, 'time')) {
                $personData[$name] = $value;
            } else {
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
        // Находим модель сотрудника со связями
        $employee = Employee::with(['user', 'person'])->find($id);

        if (! $employee) {
            return redirect()->back()->with('error', 'Сотрудник не найден');
        }

        // Удаляем связанные записи
        EmployeePosition::where('employee_id', $id)->delete();

        // Удаляем сотрудника — события сработают
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Сотрудник и все связанные данные удалены');
    }
}
