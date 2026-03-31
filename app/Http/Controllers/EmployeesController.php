<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Person;
use App\Models\Position;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkStatus;
use App\Services\JsonColumnService;
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
        $employees = Employee::orderBy('id', 'desc')->paginate(10);
        $positions = Position::all();
        $positionTypes = PositionType::has('positions')->get();

        return view('admin.employees.index', compact(
            'employees',
            'positions',
            'positionTypes',
        ));
    }

    public function create(Request $request)
    {
        $roles = Role::all();

        $backUrl = $request->input('back_url');

        $columns = Person::getTableColumns();

        return view('admin.employees.create')->with([
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
                'unique:users,login,'.($employee->user->id ?? 'NULL'),
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
                if (! empty($removedIndexes)) {
                    // удаляем по индексам (если индекс существует) и по пути (если передан)
                    foreach ($removedIndexes as $idx) {
                        if (isset($existingFiles[$idx])) {
                            // попытка удалить физически (без фатальной ошибки)
                            try {
                                if (! empty($existingFiles[$idx])) {
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
                            if (! empty($p)) {
                                Storage::disk('public')->delete($p);
                            }
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

                if (! empty($finalFiles)) {
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
