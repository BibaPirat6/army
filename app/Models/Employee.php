<?php
// app/Models/Employee.php

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
        'commissariat_id',    // добавим
        'department_id',      // добавим
        'division_id',        // добавим
    ];

    // Связи с подразделениями
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }

    public function workDays(): HasMany
    {
        return $this->hasMany(WorkDay::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    protected static function booted()
    {
        static::deleting(function ($employee) {
            $employee->load(['user', 'person']);
            if ($employee->user) {
                $employee->user->delete();
            }
            if ($employee->person) {
                $employee->person->delete();
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        if (! $this->person) {
            return 'Без ФИО';
        }

        return collect([
            $this->person->фамилия,
            $this->person->имя,
            $this->person->отчество,
        ])->filter()->implode(' ') ?: 'Без ФИО';
    }

    /**
     * Получить текущую активную должность сотрудника
     */
    public function getCurrentEmployeePosition()
    {
        return $this->employeePositions()
            ->whereHas('employeePositionStatus', function ($query) {
                $query->where('occupies_rate', true);
            })
            ->first();
    }

    public function getCurrentEmployeePositionStatusId()
    {
        $activePosition = $this->getCurrentEmployeePosition();
        return $activePosition?->employee_position_status_id ?? 1;
    }

    public function getCurrentPosition()
    {
        $activePosition = $this->getCurrentEmployeePosition();
        
        if ($activePosition && $activePosition->commissariatPosition) {
            return $activePosition->commissariatPosition->position;
        }

        return null;
    }

    public function getCurrentPositionName()
    {
        $position = $this->getCurrentPosition();
        return $position?->name;
    }

    /**
     * Получить текущую ставку
     */
    public function getCurrentRate()
    {
        $activePosition = $this->getCurrentEmployeePosition();
        return $activePosition?->rate ?? null;
    }
}