<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'person_id',
        'work_status_id',
    ];

    /**
     * Получить пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить персону
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /** обратная связь
     * Получить все должности сотрудника
     */
    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }

    protected static function booted()
    {
        static::deleting(function ($employee) {
            $employee->load(['user', 'person']);
            // При удалении сотрудника — удаляем связанного пользователя
            if ($employee->user) {
                $employee->user->delete();
            }

            // При удалении сотрудника — удаляем связанную персону
            if ($employee->person) {
                $employee->person->delete();
            }
        });
    }

    /**
     * Аксессор: полное ФИО сотрудника
     * Возвращает: "Иванов Иван Иванович"
     */
    public function getFullNameAttribute(): string
    {
        if (! $this->person) {
            return 'Без ФИО';
        }

        return collect([
            $this->person->last_name,
            $this->person->first_name,
            $this->person->patronymic,
        ])->filter()->implode(' ') ?: 'Без ФИО';
    }
}
