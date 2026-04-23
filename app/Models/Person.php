<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Schema;

class Person extends Model
{
    protected $table = 'persons';

    protected $guarded = [];

    protected $casts = [
        'combat_veteran' => 'boolean',
        'has_secondary_education' => 'boolean',
        'has_higher_education' => 'boolean',
    ];
    

    /**
     * Колонки которые НИКОГДА не должны выводиться (системные)
     */
    protected static $alwaysExclude = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Колонки которые могут выводиться опционально (ФИО)
     */
    protected static $systemColumns = ['first_name', 'last_name', 'patronymic', 'участие_в_боевых_действиях', 'возраст', 'наличие_среднего_образования','наличие_высшего_образования'];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Получить колонки таблицы
     *
     * @param  bool  $includeSystem  - включать ли системные колонки (first_name, last_name, patronymic)
     * @param  array  $additionalExclude  - дополнительные колонки для исключения
     */
    public static function getTableColumns(
        string $table = 'persons',
        bool $includeSystem = false,
        array $additionalExclude = []
    ): array {

        // Базовые колонки для исключения (никогда не выводим)
        $exclude = self::$alwaysExclude;

        // Добавляем системные колонки (ФИО) если нужно исключить
        if (! $includeSystem) {
            $exclude = array_merge($exclude, self::$systemColumns);
        }

        // Добавляем дополнительные колонки для исключения
        if (! empty($additionalExclude)) {
            $exclude = array_merge($exclude, $additionalExclude);
        }

        $columns = DB::select('
            SELECT 
                COLUMN_NAME as name,
                COLUMN_TYPE as type,
                IS_NULLABLE as nullable,
                COLUMN_DEFAULT as `default`,
                COLUMN_COMMENT as comment,
                EXTRA as extra
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = ?
            ORDER BY ORDINAL_POSITION
        ', [$table]);

        $result = [];

        foreach ($columns as $col) {
            // Пропускаем колонки которые в исключении
            if (in_array($col->name, $exclude)) {
                continue;
            }

            // Определяем тип поля для HTML
            $htmlType = 'text';
            $step = null;

            if (str_contains($col->type, 'date')) {
                $htmlType = 'date';
            } elseif (str_contains($col->type, 'int') && $col->type !== 'tinyint(1)') {
                $htmlType = 'number';
                $step = '1';
            } elseif (str_contains($col->type, 'decimal') || str_contains($col->type, 'float') || str_contains($col->type, 'double')) {
                $htmlType = 'number';
                $step = '0.01';
            } elseif (str_contains($col->type, 'tinyint(1)')) {
                $htmlType = 'checkbox';
            } elseif (str_contains($col->type, 'text')) {
                $htmlType = 'textarea';
            }

            $result[$col->name] = [
                'name' => $col->name,
                'type' => $col->type,
                'htmlType' => $htmlType,
                'step' => $step,
                'nullable' => $col->nullable === 'YES',
                'default' => $col->default,
                'comment' => $col->comment ?: null,
                'extra' => $col->extra,
            ];
        }

        return $result;
    }

    /**
     * Получить только пользовательские колонки (без системных и без ФИО)
     */
    public static function getUserColumns(string $table = 'persons'): array
    {
        return self::getTableColumns($table, false);
    }

    /**
     * Получить колонки включая ФИО (но без id, created_at, updated_at, deleted_at)
     */
    public static function getAllColumns(string $table = 'persons'): array
    {
        return self::getTableColumns($table, true);
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
