<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Schema;

class PersonColumn extends Model
{
    protected $fillable = [
        'column_name',
        'type',
        'comment_ru',
        'default',
    ];

    protected $table = 'person_columns';

    // получение колонок
    public static function getTableColumns(
        string $table = 'persons',
        array $exclude = ['id', 'created_at', 'updated_at']
    ): array {
        $columns = Schema::getColumnListing($table);
        $result = [];

        foreach ($columns as $name) {
            if (in_array($name, $exclude)) {
                continue;
            }

            $info = DB::selectOne(
                "SHOW COLUMNS FROM `$table` WHERE Field = ?",
                [$name]
            );

            $result[$name] = [
                'name' => $name,
                'type' => $info->Type ?? 'unknown',
                'nullable' => $info->Null === 'YES',
                'default' => $info->Default,
            ];
        }

        return $result;
    }

    /**
     * Возвращает подробную информацию об одной колонке таблицы
     *
     * @param  string  $columnName  имя колонки (например 'first_name')
     * @param  array  $fields  какие поля вернуть (по умолчанию основные)
     * @return array|null информация о колонке или null, если колонка не найдена
     */
    public static function getColumnInfo(string $table, string $columnName, array $fields = ['name', 'type', 'nullable', 'default', 'key', 'extra', 'comment']): ?array
    {
        $table = 'persons';

        // Проверяем существование колонки
        if (! Schema::hasColumn($table, $columnName)) {
            return null;
        }

        // Запрос к information_schema — здесь есть полный тип и collation
        $info = DB::selectOne('
        SELECT 
            COLUMN_NAME         AS name,
            COLUMN_TYPE         AS full_type,          -- ← varchar(255), longtext utf8mb4_bin, decimal(10,2) и т.д.
            DATA_TYPE           AS data_type,          -- ← чистый тип: varchar, longtext, decimal...
            COLLATION_NAME      AS collation,          -- ← utf8mb4_bin, utf8mb4_unicode_ci или null
            IS_NULLABLE         AS nullable,
            COLUMN_DEFAULT      AS `default`,
            COLUMN_KEY          AS `key`,
            EXTRA               AS extra,
            COLUMN_COMMENT      AS comment
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name   = ?
          AND column_name  = ?
    ', [$table, $columnName]);

        if (! $info) {
            return null;
        }

        $item = [];

        // Заполняем только те поля, которые запросили в $fields
        foreach ($fields as $field) {
            $key = $field === 'name' ? 'name' : $field;

            $item[$field] = match ($field) {
                'nullable' => $info->nullable === 'YES',
                'default' => $info->default !== null ? trim($info->default, "'\"") : null,
                'key' => $info->key ?? '',
                'extra' => $info->extra ?? '',
                'comment' => $info->comment ?? '',
                'type' => $info->full_type ?? 'unknown',  // ← здесь полный тип с collation
                default => $info->$key ?? null,
            };
        }

        // Дополнительно всегда добавляем чистый тип и collation (если нужно)
        $item['data_type'] = $info->data_type;
        $item['collation'] = $info->collation ?? null;

        return $item;
    }
}
