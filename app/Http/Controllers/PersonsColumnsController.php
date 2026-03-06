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
                if (array_key_exists('default', $data) && $data['default'] !== null && $data['default'] !== '') {
                    // Важно: для CURRENT_TIMESTAMP и подобных нужно передавать как выражение
                    if (strtoupper($data['default']) === 'CURRENT_TIMESTAMP') {
                        $column->default(\DB::raw('CURRENT_TIMESTAMP'));
                    } else {
                        $column->default($data['default']);
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
            $columnName = $data['column_name'];
            $type = $data['column_type'];

            // ПРОВЕРКА: колонка ДОЛЖНА существовать для изменения
            if (! Schema::hasColumn('persons', $columnName)) {
                throw new \Exception("Колонка «{$columnName}» не найдена в таблице persons.");
            }

            Schema::table('persons', function (Blueprint $table) use ($columnName, $type, $data) {
                $comment = null;
                if ($type === 'file') {
                    $comment = 'file';
                } elseif ($type === 'json') {
                    $comment = 'json';
                }

                $column = match ($type) {
                    'int' => $table->integer($columnName),
                    'decimal' => $table->decimal($columnName, 10, 2),
                    'varchar' => $table->string($columnName, 255),
                    'text' => $table->text($columnName),
                    'date' => $table->date($columnName),
                    'datetime' => $table->dateTime($columnName),
                    'file' => $table->string($columnName, 255),
                    'json' => $table->json($columnName),
                    default => throw new \Exception("Неподдерживаемый тип: $type"),
                };

                if (! empty($data['nullable'])) {
                    $column->nullable();
                } else {
                    $column->nullable(false);
                }

                if (isset($data['default']) && $data['default'] !== '') {
                    if (strtoupper($data['default']) === 'CURRENT_TIMESTAMP') {
                        $column->default(DB::raw('CURRENT_TIMESTAMP'));
                    } else {
                        $column->default($data['default']);
                    }
                } else {
                    $column->default(null);
                }

                if ($comment !== null) {
                    $column->comment($comment);
                } else {
                    $column->comment(null);
                }

                $column->change();
            });

            $backUrl = $request->input('backUrl');

            return redirect($backUrl ?? route('persons-columns.index'))
                ->with('success', "Колонка «{$columnName}» успешно изменена.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Не удалось изменить колонку: '.$e->getMessage()]);
        }
    }
}
