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



     /**
     * Получить ID текущего статуса сотрудника (активное назначение)
     * Возвращает employee_position_status_id из активного назначения
     */
    public function getCurrentEmployeePositionStatusId()
    {
        // Получаем активное назначение сотрудника (которое занимает ставку)
        $activePosition = $this->employeePositions()
            ->whereHas('employeePositionStatus', function($query) {
                $query->where('occupies_rate', true);
            })
            ->first();
        
        if ($activePosition) {
            return $activePosition->employee_position_status_id;
        }
        
        // Если нет активного назначения, возвращаем статус по умолчанию (например, 1 - работает)
        return 1;
    }
    
    /**
     * Получить текущую должность сотрудника
     */
    public function getCurrentPosition()
    {
        $activePosition = $this->employeePositions()
            ->whereHas('employeePositionStatus', function($query) {
                $query->where('occupies_rate', true);
            })
            ->with('commissariatPosition.position')
            ->first();
        
        if ($activePosition && $activePosition->commissariatPosition) {
            return $activePosition->commissariatPosition->position;
        }
        
        return null;
    }
    
    /**
     * Получить название текущей должности
     */
    public function getCurrentPositionName()
    {
        $position = $this->getCurrentPosition();
        return $position ? $position->name : null;
    }
}
