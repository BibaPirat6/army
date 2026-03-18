<?php

namespace App\Http\Controllers;

use App\Models\PersonColumn;
use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Schema;

class PersonsColumnsController extends Controller
{
    public function index(Request $request)
    {
        $backUrl = $request->input('back_url');

        $columns = PersonColumn::getTableColumns('persons');

        return view('admin.persons.persons-columns.index', compact('backUrl', 'columns'));
    }

    public function create(Request $request)
    {
        $backUrl = $request->input('back_url');

        return view('admin.persons.persons-columns.create', compact('backUrl'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'column_name' => 'required|string|regex:/^[a-z0-9_]+$/|max:64',
            'column_type' => 'required|string|in:integer,decimal,string,text,json,date,datetime,blob',
            'comment_ru' => 'required|nullable|string|max:255',
            'default' => 'nullable|string|max:255',
        ], [
            'column_name.regex' => 'Имя колонки может содержать только латинские буквы, цифры и подчёркивание.',
            'column_name.max' => 'Имя колонки слишком длинное (максимум 64 символа).',

            'column_name.required' => 'Поле "Имя колонки" обязательно для заполнения.',
            'column_type.required' => 'Поле "Тип колонки" обязательно для заполнения.',
            'comment_ru.required' => 'Поле "Имя на русском" обязательно для заполнения.',
        ]);

        try {
            $columnName = $data['column_name'];

            if (Schema::hasColumn('persons', $columnName)) {
                throw new \Exception('колонка уже существует');
            }

            Schema::table('persons', function (Blueprint $table) use ($data) {
                $type = $data['column_type'];
                $name = $data['column_name'];

                $column = match ($type) {
                    'integer' => $table->integer($name)->nullable(),
                    'decimal' => $table->decimal($name, 3, 2)->nullable(),
                    'string' => $table->string($name)->nullable(),
                    'text' => $table->text($name)->nullable(),
                    'json' => $table->longText($name)->nullable(),
                    'date' => $table->date($name)->nullable(),
                    'datetime' => $table->dateTime($name)->nullable(),
                    'blob' => $table->binary($name)->nullable(),
                    default => throw new \Exception("Неподдерживаемый тип: {$type}"),
                };

                if (array_key_exists('default', $data) && $data['default'] !== null && $data['default'] !== '') {
                    $def = trim($data['default']);
                    $column->default($def);
                }

                if (! empty($data['comment_ru'])) {
                    $column->comment($data['comment_ru']);
                }
            });

              // После создания колонки, если это blob, меняем тип
    if ($data['column_type'] === 'blob') {
        DB::statement("ALTER TABLE `persons` MODIFY `{$data['column_name']}` LONGBLOB NULL");
    }

            PersonColumn::create([
                'column_name' => $columnName,
                'type' => $data['column_type'],
                'comment_ru' => $data['comment_ru'],
                'nullable' => ! empty($data['nullable']),
                'default' => $data['default'] ?? null,
            ]);

            $backUrl = $request->input('backUrl');

            return redirect($backUrl ?? route('persons-columns.index'))
                ->with('success', 'Колонка успешно создана.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании колонки: '.$e->getMessage()]);
        }
    }

    public function edit(Request $request, $id)
    {
        $backUrl = $request->input('back_url');
        $columnName = $id;

        $column = PersonColumn::getColumnInfo('persons', $columnName);

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
                'comment_ru' => 'required|nullable|string|max:255',
            ],
            [
                'column_name.required' => 'Имя колонки обязательно.',
                'column_name.string' => 'Имя колонки должно быть текстом.',
                'comment_ru.required' => 'Поле "Имя на русском" обязательно для заполнения.',
            ]
        );

        $table = 'persons';
        $oldName = $id;
        $newName = $data['column_name'];

        try {
            // Если имя изменилось - переименовываем
            if ($oldName !== $newName) {
                Schema::table($table, function (Blueprint $table) use ($oldName, $newName) {
                    $table->renameColumn($oldName, $newName);
                });
            }

            // Получаем информацию о колонке (уже по новому имени!)
            $column = DB::selectOne('
            SELECT COLUMN_TYPE, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ', [$table, $newName]); // Используем $newName, а не $oldName!

            if (! $column) {
                throw new \Exception('Колонка не найдена в базе данных');
            }

            $type = $column->COLUMN_TYPE;
            $dataType = $column->DATA_TYPE;

            $defaultSql = '';
            $value = $data['default'] ?? null;

            if ($value !== null && $value !== '') {
                // Пользователь явно указал default
                if (in_array($dataType, ['date', 'datetime', 'timestamp']) && strtoupper($value) === 'CURRENT_TIMESTAMP') {
                    $defaultSql = 'DEFAULT CURRENT_TIMESTAMP';
                } elseif ($dataType === 'json' || str_contains($type, 'blob')) {
                    // BLOB/JSON не поддерживают default
                } else {
                    $defaultSql = "DEFAULT '".addslashes($value)."'";
                }
            } else {
                // Пользователь очистил поле default
                if (in_array($dataType, ['varchar', 'text', 'char'])) {
                    $defaultSql = "DEFAULT ''"; // пустая строка
                } elseif (in_array($dataType, ['int', 'decimal', 'float'])) {
                    $defaultSql = 'DEFAULT 0'; // число по умолчанию
                } elseif (in_array($dataType, ['date', 'datetime', 'timestamp'])) {
                    $defaultSql = ''; // оставляем без default (NULL)
                }
            }

            $commentSql = '';
            if (! empty($data['comment_ru'])) {
                $commentSql = "COMMENT '".addslashes($data['comment_ru'])."'";
            }

            DB::statement("
            ALTER TABLE `$table`
            MODIFY `$newName` $type $defaultSql $commentSql
        ");

            return redirect()->route('persons-columns.index')
                ->with('success', "Колонка '{$newName}' успешно обновлена");

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('persons-columns.index')
                ->withErrors(['error' => 'Ошибка: '.$e->getMessage()]);
        }
    }

    public function delete(Request $request, $id)
    {
        $columnName = $id;

        try {
            // 1. Проверяем, существует ли колонка в таблице persons
            if (! Schema::hasColumn('persons', $columnName)) {
                throw new \Exception("Колонка «{$columnName}» не найдена в таблице persons.");
            }

            // 2. Проверяем, существует ли запись в person_columns
            $columnRecord = PersonColumn::where('column_name', $columnName)->first();
            if (! $columnRecord) {
                throw new \Exception("Запись о колонке «{$columnName}» не найдена в справочнике.");
            }

            // 3. Удаляем колонку из таблицы persons
            Schema::table('persons', function (Blueprint $table) use ($columnName) {
                $table->dropColumn($columnName);
            });

            // 4. Удаляем ЗАПИСЬ из таблицы person_columns (не колонку!)
            $columnRecord->delete();

            $backUrl = $request->get('backUrl', route('persons-columns.index'));

            return redirect()->to($backUrl)
                ->with('success', "Колонка «{$columnName}» успешно удалена!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
