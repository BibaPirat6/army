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
            'column_name' => 'required|string',
            'column_type' => 'required|string',
            'default' => 'nullable|string',
            'nullable' => 'nullable|sometimes|in:1',
        ], [
            'column_name.required' => 'Поле "Имя колонки" обязательно для заполнения.',
            'column_type.required' => 'Поле "Тип колонки" обязательно для заполнения.',
        ]);

        try {
            $columnName = $data['column_name'];

            if (Schema::hasColumn('persons', $columnName)) {
                throw new \Exception('колонка уже существует в persons');
            }

            $existingMeta = PersonColumn::where('column_name', $columnName)->first();
            if ($existingMeta) {
                throw new \Exception("Колонка с именем '{$columnName}' уже существует в справочнике в persons_columns");
            }

            if (in_array($data['column_type'], ['json', 'file']) && ! empty($data['default'])) {
                throw new \Exception('Для полей типа JSON и FILE нельзя указать значение по умолчанию');
            }

            $isNullable = $request->has('nullable');

            $commentValue = match ($data['column_type']) {
                'json' => 'json',
                'file' => 'file',
                default => null,
            };

            Schema::table('persons', function (Blueprint $table) use ($data, $commentValue, $isNullable) {
                $type = $data['column_type'];
                $name = $data['column_name'];

                $column = match ($type) {
                    'integer' => $table->integer($name)->nullable(),
                    'decimal' => $table->decimal($name, 4, 2)->nullable(),
                    'varchar' => $table->string($name)->nullable(),
                    'json' => $table->longText($name)->nullable(),
                    'date' => $table->date($name)->nullable(),
                    'file' => $table->longText($name)->nullable(),
                    default => throw new \Exception("Неподдерживаемый тип: {$type}"),
                };

                if ($isNullable) {
                    $column->nullable();
                } else {
                    $column->nullable(false);
                }

                if ($commentValue) {
                    $column->comment($commentValue);
                }

                if (array_key_exists('default', $data) && $data['default'] !== null && $data['default'] !== '') {
                    $def = trim($data['default']);
                    $column->default($def);
                }
            });

            PersonColumn::create([
                'column_name' => $columnName,
                'type' => $data['column_type'],
                'default' => $data['default'] ?? null,
                'comment' => $commentValue,
                'nullable' => $isNullable,
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
                'default' => 'nullable',
                'nullable' => 'nullable|sometimes|in:1',
            ],
            [
                'column_name.required' => 'Имя колонки обязательно.',
                'column_name.string' => 'Имя колонки должно быть текстом.',
            ]
        );

        $table = 'persons';
        $oldName = $id;
        $newName = $data['column_name'];
        $isNullable = $request->has('nullable');

        try {
            if ($oldName !== $newName) {
                Schema::table($table, function (Blueprint $table) use ($oldName, $newName) {
                    $table->renameColumn($oldName, $newName);
                });

                PersonColumn::where('column_name', $oldName)
                    ->update(['column_name' => $newName]);
            }

            $column = PersonColumn::getColumnInfo($table, $newName);

            if (! $column) {
                throw new \Exception('Колонка не найдена');
            }

            $type = $column['type'];
            $default = $data['default'] ?? null;

            $nullableSql = $isNullable ? 'NULL' : 'NOT NULL';

            $defaultSql = '';
            if ($default !== null && $default !== '') {
                $defaultSql = "DEFAULT '".addslashes($default)."'";
            }

            DB::statement("
            ALTER TABLE `$table`
            MODIFY `$newName` $type $nullableSql $defaultSql
        ");

            PersonColumn::where('column_name', $newName)->update([
                'default' => $default,
                'nullable' => $isNullable,
                'column_name' => $data['column_name'] ?? null,
            ]);

            return redirect($request->input('backUrl') ?? route('persons-columns.index'))
                ->with('success', 'Колонка обновлена');

        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
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

            return redirect($backUrl ?? route('persons-columns.index'))
                ->with('success', 'Колонка удалена.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
