<?php

namespace App\Http\Controllers;

use App\Models\Person;
use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Schema;

class PersonsColumnsController extends Controller
{
    public function index(Request $request)
    {
        $backUrl = $request->input('back_url');

        $columns = Person::getTableColumns();

        return view('admin.persons.persons-columns.index', compact('backUrl', 'columns'));
    }

    public function create(Request $request)
    {
        $backUrl = $request->input('back_url');

        return view('admin.persons.persons-columns.create', compact('backUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'column_name' => 'required|string',
                'column_type' => 'required|string|in:int,decimal,varchar,text,date,datetime,file,json',
                'default' => 'sometimes|nullable',
                'nullable' => 'sometimes|in:1',
            ],
            [
                // Сообщения для column_name
                'column_name.required' => 'Имя колонки обязательно.',
                'column_name.string' => 'Имя колонки должно быть текстом.',

                // Сообщения для column_type
                'column_type.required' => 'Выберите тип данных для колонки.',
                'column_type.string' => 'Тип данных указан неверно.',
                'column_type.in' => 'Недопустимый тип данных. Доступны: int, decimal, varchar, text, date, datetime,file,json.',

                // Сообщения для default
                'default.string' => 'Значение по умолчанию должно быть текстом.',

                // Сообщения для nullable
                'nullable.in' => 'Неверное значение для поля NULL (должно быть 1 или пусто).',
            ]
        );

        try {
            Schema::table('persons', function (Blueprint $table) use ($data) {
                $type = $data['column_type'];
                $columnName = $data['column_name'];
                if (Schema::hasColumn('persons', $columnName)) {
                    throw new \Exception("Колонка «{$columnName}» уже существует в таблице persons.");
                }

                // Определяем тип колонки и комментарий
                $comment = null;
                if ($type === 'file') {
                    $comment = 'file';
                } elseif ($type === 'json') {
                    $comment = 'json';
                }

                // 1. Создаём базовую колонку в зависимости от типа
                $column = match ($type) {
                    'int' => $table->integer($columnName),
                    'decimal' => $table->decimal($columnName, 3, 2),
                    'text' => $table->text($columnName),
                    'varchar' => $table->string($columnName),
                    'date' => $table->date($columnName)->nullable(),
                    'datetime' => $table->dateTime($columnName)->nullable(),
                    'file' => $table->string($columnName, 255)->nullable(),
                    'json' => $table->json($columnName)->nullable(),
                    default => throw new \Exception("Неподдерживаемый тип: $type"),
                };

                // 2. Применяем модификаторы
                if (! empty($data['nullable'])) {
                    $column->nullable();
                }

                // 3. Default значение
                // Default значение
                if (array_key_exists('default', $data)) {
                    $def = trim($data['default'] ?? '');

                    if ($def === '' || $def === null) {
                        $column->default(null);
                    } elseif (strtoupper($def) === 'CURRENT_TIMESTAMP') {
                        $column->default(DB::raw('CURRENT_TIMESTAMP'));
                    } else {
                        // Для всех типов — используем DB::raw с правильным экранированием
                        $escaped = addslashes($def);  // экранируем только опасные символы
                        $column->default(DB::raw("'{$escaped}'"));
                    }
                }

                // Устанавливаем комментарий
                if ($comment !== null) {
                    $column->comment($comment);
                }
            });
            $backUrl = $request->input('backUrl');

            return redirect($backUrl ?? route('persons-columns.index'))->with('success', 'Колонка успешно создана.');
        } catch (\Exception $e) {
            return redirect(route('persons-columns.create'))->withErrors(['error' => 'Не удалось добавить колонку: '.$e->getMessage()]);
        }

    }

    public function edit(Request $request, $id)
    {
        $backUrl = $request->input('back_url');
        $columnName = $id;

        // Получаем данные о колонке
        $column = Person::getColumnInfo($columnName);

        if (! $column) {
            return redirect()->back()->withErrors(['error' => "Колонка «{$columnName}» не найдена"]);
        }

        return view('admin.persons.persons-columns.edit', compact('backUrl', 'column'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate(
            [
                'column_name' => 'required|string',
                'label' => 'sometimes|nullable|string',
                'default' => 'sometimes|nullable',
                'nullable' => 'sometimes|in:1',
            ],
            [
                'column_name.required' => 'Имя колонки обязательно.',
                'column_name.string' => 'Имя колонки должно быть текстом.',
            ]
        );

        try {

            $oldName = $id;
            $newName = $data['column_name'];

            /*
            |--------------------------------------------------------------------------
            | 1. Переименование колонки
            |--------------------------------------------------------------------------
            */

            if ($oldName !== $newName) {
                Schema::table('persons', function (Blueprint $table) use ($oldName, $newName) {
                    $table->renameColumn($oldName, $newName);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Получаем текущий тип колонки
            |--------------------------------------------------------------------------
            */

            $column = DB::selectOne("
            SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = 'persons'
            AND COLUMN_NAME = ?
        ", [$newName]);

            $columnType = $column->COLUMN_TYPE;

            /*
            |--------------------------------------------------------------------------
            | 3. Формируем SQL изменения
            |--------------------------------------------------------------------------
            */

            $nullable = ! empty($data['nullable']) ? 'NULL' : 'NOT NULL';

            $default = '';

            if (array_key_exists('default', $data) && $data['default'] !== null && $data['default'] !== '') {
                $value = addslashes($data['default']);
                $default = "DEFAULT '{$value}'";
            }

            $comment = '';
            if (! empty($data['label'])) {
                $label = addslashes($data['label']);
                $comment = "COMMENT '{$label}'";
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Выполняем изменение
            |--------------------------------------------------------------------------
            */

            DB::statement("
            ALTER TABLE persons 
            MODIFY `$newName` $columnType $nullable $default $comment
        ");

            return redirect($request->input('backUrl') ?? route('persons-columns.index'))
                ->with('success', "Колонка обновлена → {$newName}");

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function delete(Request $request, $id)
    {
        $columnName = $id;

        try {
            if (! Schema::hasColumn('persons', $columnName)) {
                throw new \Exception("Колонка «{$columnName}» не найдена в таблице persons.");
            }

            Schema::table('persons', function (Blueprint $table) use ($columnName) {
                $table->dropColumn($columnName);
            });

            $backUrl = $request->get('backUrl', route('persons-columns.index'));

            return redirect()->to($backUrl)
                ->with('success', "Колонка «{$columnName}» успешно удалена!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
