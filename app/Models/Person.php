<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Person extends Model
{
    protected $table = "persons";

    protected $fillable = [
        "first_name",
        "last_name",
        "patronymic",
        "phones",
        "emails",
        "photo",
    ];
    protected $casts = [
        'phones' => 'array',
        'emails' => 'array',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }


    // получение колонок
    public static function getTableColumns()
    {
        // Получаем экземпляр модели
        $instance = new static;

        // Получаем имя таблицы
        $table = $instance->getTable();

        // Получаем список колонок
        return Schema::getColumnListing($table);
    }
}
