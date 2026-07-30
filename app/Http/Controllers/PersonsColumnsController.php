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

        $columns = Person::getUserColumns('persons');

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
            $columnName = $this->normalizeFieldName($data['column_name']);

            if (Schema::hasColumn('persons', $columnName)) {
                throw new \Exception('колонка уже существует в persons');
            }

            if (in_array($data['column_type'], ['json', 'file', 'date']) && ! empty($data['default'])) {
                throw new \Exception('Для полей типа JSON, FILE и DATE нельзя указать значение по умолчанию');
            }

            $isNullable = $request->has('nullable');

            $commentValue = match ($data['column_type']) {
                'json' => 'json',
                'file' => 'file',
                default => null,
            };

            Schema::table('persons', function (Blueprint $table) use ($data, $commentValue, $isNullable) {
                $type = $data['column_type'];
                $name = $this->normalizeFieldName($data['column_name']);

                $column = match ($type) {
                    'integer' => $table->integer($name),
                    'decimal' => $table->decimal($name, 4, 2),
                    'varchar' => $table->string($name),
                    'json' => $table->longText($name),
                    'date' => $table->date($name),
                    'file' => $table->longText($name),
                    default => throw new \Exception("Неподдерживаемый тип: {$type}"),
                };

                if ($isNullable || $type === 'date') {
                    $column->nullable();
                }

                if ($commentValue) {
                    $column->comment($commentValue);
                }

                if (array_key_exists('default', $data) && $data['default'] !== null && $data['default'] !== '') {
                    $def = trim($data['default']);
                    $column->default($def);
                }
            });

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

        $column = Person::getColumnInfo('persons', $columnName);

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
        $newName = $this->normalizeFieldName($data['column_name']);
        $isNullable = $request->has('nullable');

        try {

            /**
             * 1. RENAME COLUMN
             */
            if ($oldName !== $newName) {
                Schema::table($table, function (Blueprint $table) use ($oldName, $newName) {
                    $table->renameColumn($oldName, $newName);
                });
            }

            /**
             * 2. COLUMN INFO
             */
            $column = Person::getColumnInfo($table, $newName);

            if (! $column) {
                throw new \Exception('Колонка не найдена');
            }

            $dataType = $column['data_type']; // <<< ВАЖНО (НЕ full type)
            $comment = $column['comment'] ?? null;

            $default = $data['default'] ?? null;
            if ($default === '') {
                $default = null;
            }

            $currentNullable = $column['nullable'];
            $newNullable = $isNullable;

            /**
             * 3. HANDLE NULL → NOT NULL
             */
            if ($currentNullable && ! $newNullable) {

                $nullCount = DB::table($table)
                    ->whereNull($newName)
                    ->count();

                if ($nullCount > 0) {

                    $fillValue = $default;

                    // если default не задан — ставим безопасный fallback
                    if ($fillValue === null || $fillValue === '') {

                        // универсальный fallback по типу MySQL-логики
                        $fillValue = match ($column['data_type']) {
                            'date' => now()->format('Y-m-d'),
                            'int', 'integer' => 0,
                            'decimal' => 0,
                            default => '',
                        };
                    }

                    DB::table($table)
                        ->whereNull($newName)
                        ->update([$newName => $fillValue]);
                }
            }

            /**
             * 4. SAFE TYPE MAP (CRITICAL FIX FOR DATE)
             */
            $type = match ($dataType) {
                'date' => 'DATE',
                'int' => 'INT',
                'integer' => 'INT',
                'varchar' => 'VARCHAR(255)',
                'decimal' => 'DECIMAL(10,2)',
                'text' => 'TEXT',
                'longtext' => 'LONGTEXT',
                default => strtoupper($dataType),
            };

            /**
             * 5. BUILD ALTER
             */
            $parts = [];

            $parts[] = $type;
            $parts[] = $newNullable ? 'NULL' : 'NOT NULL';

            if ($default !== null && $default !== '') {
                $parts[] = "DEFAULT '".addslashes($default)."'";
            }

            if ($comment) {
                $parts[] = "COMMENT '".addslashes($comment)."'";
            }

            $sql = implode(' ', $parts);

            DB::statement("
            ALTER TABLE `$table`
            MODIFY `$newName` $sql
        ");

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
            if (! Schema::hasColumn('persons', $columnName)) {
                throw new \Exception("Колонка «{$columnName}» не найдена в таблице persons.");
            }

            Schema::table('persons', function (Blueprint $table) use ($columnName) {
                $table->dropColumn($columnName);
            });

            $backUrl = $request->get('backUrl', route('persons-columns.index'));

            return redirect($backUrl ?? route('persons-columns.index'))
                ->with('success', 'Колонка удалена.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function normalizeFieldName(string $name): string
    {
        return mb_strtolower(
            preg_replace('/\s+/', '_', trim($name))
        );
    }
}
