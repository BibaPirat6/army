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
        $data = $request->validate([
            'column_name' => 'required|string|regex:/^[a-z0-9_]+$/|max:64',
            'column_type' => 'required|string|in:integer,decimal,string,text,json,date,datetime,mediumBlob,longBlob',
            'comment_ru' => 'required|nullable|string|max:255',
            'default' => 'nullable|string|max:255',
            'nullable' => 'sometimes|in:1',
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
                throw new \Exception("колонка уже существует");
            }

            Schema::table('persons', function (Blueprint $table) use ($data) {
                $type = $data['column_type'];
                $name = $data['column_name'];

                $column = match ($type) {
                    'integer' => $table->integer($name),
                    'decimal' => $table->decimal($name, 3, 2),   // можно сделать настраиваемым позже
                    'string' => $table->string($name),
                    'text' => $table->text($name),
                    'json' => $table->json($name),
                    'date' => $table->date($name),
                    'datetime' => $table->dateTime($name),
                    'mediumBlob' => $table->mediumBinary($name),     // mediumBlob
                    'longBlob' => $table->longBinary($name),       // longBlob
                    default => throw new \Exception("Неподдерживаемый тип: {$type}"),
                };

                if (! empty($data['nullable'])) {
                    $column->nullable();
                }

                // Значение по умолчанию
                if (array_key_exists('default', $data) && $data['default'] !== null) {
                    $def = trim($data['default']);

                    if (strtoupper($def) === 'NULL') {
                        $column->default(null);
                    } elseif (strtoupper($def) === 'CURRENT_TIMESTAMP') {
                        $column->default(DB::raw('CURRENT_TIMESTAMP'));
                    } else {
                        $column->default($def);
                    }
                }

                // Комментарий — только то, что ввёл пользователь (русский текст)
                if (! empty($data['comment_ru'])) {
                    $column->comment($data['comment_ru']);
                }
            });

            // Сохраняем метаданные колонки в persons_columns
            Person::create([
                'column_name' => $columnName,
                'type' => $data['column_type'],
                'comment_ru' => $data['comment_ru'],
                'nullable' => ! empty($data['nullable']),
                'default' => $data['default'] ?? null,
                // другие поля, если есть
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
