<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Person;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\User;
use App\Services\JsonColumnService;
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
        $employees = Employee::orderBy('id', 'desc')->paginate(10);
        $positions = Position::all();
        $positionTypes = PositionType::has('positions')->get();

        return view('admin.employees.index', compact(
            'employees',
            'positions',
            'positionTypes',
        ));
    }

    public function show(Request $request, $id)
    {
        $backUrl = $request->input('back_url');
        $employee = Employee::findOrFail($id);
        $columns = Person::getAllColumns();

        return view('admin.employees.show')->with([
            'employee' => $employee,
            'backUrl' => $backUrl,
            'columns' => $columns,
        ]);
    }

    public function create(Request $request)
    {
        // со структуры получаем по кнопке добавления сотрудника
        $commissariatId = $request->input('commissariat_id');
        $departmentId = $request->input('department_id');
        $divisionId = $request->input('division_id');

        $roles = Role::all();
        $backUrl = $request->input('back_url');
        $columns = Person::getAllColumns();

        return view('admin.employees.create')->with([
            'roles' => $roles,
            'backUrl' => $backUrl,
            'columns' => $columns,
            'commissariatId' => $commissariatId,
            'departmentId' => $departmentId,
            'divisionId' => $divisionId,
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

        $columns = Person::getAllColumns();
        $personData = [];

        foreach ($columns as $column) {
            $name = $column['name'];
            $type = $column['type'];
            $value = $request->input($name);
            $comment = $column['comment'] ?? null;
            $nullable = $column['nullable'];
            $default = $column['default'];

            // исключаем системные поля
            if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            // ========== 1. ФАЙЛЫ ==========
            if (str_contains($type, 'longtext') && in_array($comment, ['file', 'multiple', 'single'])) {
                $uploadedFiles = $this->jsonService->handleFiles(
                    $request->file($name),
                    $name
                );
                $personData[$name] = $uploadedFiles !== null
                    ? json_encode($uploadedFiles, JSON_UNESCAPED_SLASHES)
                    : null;

                continue;
            }

            // ========== 2. JSON СПИСКИ ==========
            if (str_contains($type, 'longtext') && $comment === 'json') {
                if ($value === null || trim((string) $value) === '' || trim((string) $value) === '[]') {
                    $personData[$name] = null;
                } else {
                    // Если пришла JSON строка, декодируем
                    if (is_string($value) && str_starts_with($value, '[')) {
                        $decoded = json_decode($value, true);
                        if (is_array($decoded)) {
                            $value = implode("\n", $decoded);
                        }
                    }
                    // Разбиваем строку по переносам строк
                    $lines = explode("\n", $value);
                    $lines = array_filter(array_map('trim', $lines), fn ($line) => $line !== '');
                    $personData[$name] = json_encode(array_values($lines), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                continue;
            }

            // ========== 3. BOOLEAN ПОЛЯ ==========
            if (str_contains($type, 'tinyint(1)') || $type === 'boolean') {
                $personData[$name] = ($value === '1' || $value === 1 || $value === true) ? 1 : 0;

                continue;
            }

            // ========== 4. DATE ПОЛЯ ==========
            if (str_contains($type, 'date')) {
                if (empty($value)) {
                    $personData[$name] = $nullable ? null : ($default ?? null);
                } else {
                    try {
                        $date = \Carbon\Carbon::parse($value);
                        $personData[$name] = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        $personData[$name] = $nullable ? null : ($default ?? null);
                    }
                }

                continue;
            }

            // ========== 5. DECIMAL (числа с плавающей точкой) ==========
            if (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                if ($value === null || $value === '') {
                    $personData[$name] = $nullable ? null : 0;
                } else {
                    $personData[$name] = (float) str_replace(',', '.', $value);
                }

                continue;
            }

            // ========== 6. INTEGER (целые числа, кроме tinyint(1)) ==========
            if (str_contains($type, 'int') && ! str_contains($type, 'tinyint(1)')) {
                if ($value === null || $value === '') {
                    $personData[$name] = $nullable ? null : 0;
                } else {
                    $personData[$name] = (int) $value;
                }

                continue;
            }

            // ========== 7. TEXTAREA / TEXT / LONGTEXT ==========
            if (str_contains($type, 'text') || str_contains($type, 'longtext') || str_contains($type, 'mediumtext')) {
                if ($value === null || $value === '') {
                    $personData[$name] = $nullable ? null : '';
                } else {
                    $personData[$name] = $value;
                }

                continue;
            }

            // ========== 8. ОБЫЧНЫЕ ПОЛЯ (varchar, char и т.д.) ==========
            if ($value === null || $value === '') {
                if ($nullable) {
                    $personData[$name] = null;
                } elseif ($default !== null) {
                    continue;
                } else {
                    $personData[$name] = '';
                }
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
        $employee->refresh();

        $employeeId = $employee->id;

        // Получаем параметры контекста
        $backUrl = $request->get('backUrl', route('employees.index'));
        $commissariatId = $request->input('commissariatId');
        $departmentId = $request->input('departmentId');
        $divisionId = $request->input('divisionId');

        // Определяем, нужно ли перенаправить на создание должности
        $hasContext = $commissariatId || $departmentId || $divisionId;

        if ($hasContext) {
            // Собираем параметры для создания должности
            $positionParams = [
                'id' => $employeeId,
                'employeeId' => $employeeId,
                'backUrl' => $backUrl,
            ];

            if ($commissariatId) {
                $positionParams['commissariatId'] = $commissariatId;
            }

            if ($departmentId) {
                $positionParams['departmentId'] = $departmentId;
            }

            if ($divisionId) {
                $positionParams['divisionId'] = $divisionId;
            }

            // Перенаправляем на создание должности
            return redirect()->route('employee-positions.create', $positionParams)
                ->with('success', 'Сотрудник успешно создан! Теперь назначьте должность.');
        }

        // Если нет контекста, просто возвращаемся назад
        return redirect()->to($backUrl)
            ->with('success', 'Сотрудник успешно создан!');
    }

    public function edit(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $roles = Role::all();

        $backUrl = $request->input('back_url');

        $columns = Person::getAllColumns();

        return view('admin.employees.edit')->with([
            'employee' => $employee,
            'roles' => $roles,
            'backUrl' => $backUrl,
            'columns' => $columns,
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with(['person', 'user'])->findOrFail($id);

        $data = $request->validate([
            'login' => [
                'required',
                'min:5',
                'max:255',
                'unique:users,login,'.($employee->user->id ?? 'NULL'),
            ],
            'password' => [
                'nullable',
                'min:5',
                'max:255',
            ],
            'role' => 'required|exists:roles,id',
        ], [
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
        $columns = Person::getAllColumns();
        $personData = [];

        foreach ($columns as $column) {
            $name = $column['name'];
            $type = $column['type'];
            $value = $request->input($name);
            $comment = $column['comment'] ?? null;
            $nullable = $column['nullable'];
            $default = $column['default'];

            // исключаем системные поля
            if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            // ========== 1. ФАЙЛЫ (редактирование) ==========
            if (str_contains($type, 'longtext') && in_array($comment, ['file', 'multiple', 'single'])) {
                // индексы/пути отмеченных для удаления
                $removedIndexes = (array) $request->input("removed_{$name}_indexes", []);
                $removedPaths = (array) $request->input("removed_{$name}_existing_paths", []);

                // текущие файлы из БД
                $existingFiles = [];
                if ($person && $person->$name) {
                    $existingFiles = json_decode($person->$name, true) ?? [];
                }

                // обрабатываем удаление отмеченных существующих файлов
                if (! empty($removedIndexes)) {
                    foreach ($removedIndexes as $idx) {
                        if (isset($existingFiles[$idx])) {
                            try {
                                if (! empty($existingFiles[$idx])) {
                                    Storage::disk('public')->delete($existingFiles[$idx]);
                                }
                            } catch (\Throwable $e) {
                                // silent
                            }
                            unset($existingFiles[$idx]);
                        }
                    }
                    foreach ($removedPaths as $p) {
                        try {
                            if (! empty($p)) {
                                Storage::disk('public')->delete($p);
                            }
                        } catch (\Throwable $e) {
                        }
                    }
                    $existingFiles = array_values($existingFiles);
                }

                // обработка новых загруженных файлов
                $newFiles = $this->jsonService->handleFiles(
                    $request->file($name),
                    $name
                );

                if ($newFiles === null && empty($removedIndexes)) {
                    continue;
                }

                $newFiles = $newFiles === null ? [] : (is_array($newFiles) ? $newFiles : []);
                $finalFiles = array_merge($existingFiles, $newFiles);

                if (! empty($finalFiles)) {
                    $personData[$name] = json_encode($finalFiles, JSON_UNESCAPED_SLASHES);
                } else {
                    $personData[$name] = $nullable ? null : json_encode([], JSON_UNESCAPED_SLASHES);
                }

                continue;
            }

            // ========== 2. JSON СПИСКИ ==========
            if (str_contains($type, 'longtext') && $comment === 'json') {
                if ($value === null || trim((string) $value) === '' || trim((string) $value) === '[]') {
                    $personData[$name] = $nullable ? null : json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    // Если пришла JSON строка, декодируем
                    if (is_string($value) && str_starts_with($value, '[')) {
                        $decoded = json_decode($value, true);
                        if (is_array($decoded)) {
                            $value = implode("\n", $decoded);
                        }
                    }
                    // Разбиваем строку по переносам строк
                    $lines = explode("\n", $value);
                    $lines = array_filter(array_map('trim', $lines), fn ($line) => $line !== '');
                    $personData[$name] = json_encode(array_values($lines), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                continue;
            }

            // ========== 3. BOOLEAN ПОЛЯ ==========
            if (str_contains($type, 'tinyint(1)') || $type === 'boolean') {
                $personData[$name] = ($value === '1' || $value === 1 || $value === true) ? 1 : 0;

                continue;
            }

            // ========== 4. DATE ПОЛЯ ==========
            if (str_contains($type, 'date')) {
                if (empty($value)) {
                    $personData[$name] = $nullable ? null : ($default ?? null);
                } else {
                    try {
                        $date = \Carbon\Carbon::parse($value);
                        $personData[$name] = $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        $personData[$name] = $nullable ? null : ($default ?? null);
                    }
                }

                continue;
            }

            // ========== 5. DECIMAL (числа с плавающей точкой) ==========
            if (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
                if ($value === null || $value === '') {
                    $personData[$name] = $nullable ? null : 0;
                } else {
                    $personData[$name] = (float) str_replace(',', '.', $value);
                }

                continue;
            }

            // ========== 6. INTEGER (целые числа, кроме tinyint(1)) ==========
            if (str_contains($type, 'int') && ! str_contains($type, 'tinyint(1)')) {
                if ($value === null || $value === '') {
                    $personData[$name] = $nullable ? null : 0;
                } else {
                    $personData[$name] = (int) $value;
                }

                continue;
            }

            // ========== 7. TEXTAREA / TEXT / LONGTEXT ==========
            if (str_contains($type, 'text') || str_contains($type, 'longtext') || str_contains($type, 'mediumtext')) {
                if ($value === null || $value === '') {
                    $personData[$name] = $nullable ? null : '';
                } else {
                    $personData[$name] = $value;
                }

                continue;
            }

            // ========== 8. ОБЫЧНЫЕ ПОЛЯ (varchar, char и т.д.) ==========
            if ($value === null || $value === '') {
                if ($nullable) {
                    $personData[$name] = null;
                } elseif ($default !== null) {
                    continue;
                } else {
                    $personData[$name] = '';
                }
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
