<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $table = 'departments';
    protected $fillable = ['name', 'commissariat_id'];

    // ===== ОТНОШЕНИЯ =====

    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Штатные должности в этом отделе
     */
    public function commissariatPositions(): HasMany
    {
        return $this->hasMany(CommissariatPosition::class);
    }

    /**
     * Назначения сотрудников в этом отделе (через штатные должности)
     */
    public function employeePositions(): HasManyThrough
    {
        return $this->hasManyThrough(
            EmployeePosition::class,
            CommissariatPosition::class,
            'department_id',
            'commissariat_position_id'
        );
    }

    /**
     * Штатная должность начальника отдела
     */
    public function chiefCommissariatPosition(): HasOne
    {
        return $this->hasOne(CommissariatPosition::class)
            ->whereNull('division_id')
            ->whereHas('position.chiefType', fn($q) => $q->where('name', 'начальник отдела'));
    }

    // ===== АКСЕССОРЫ =====

    public function getChiefAttribute()
    {
        return $this->chiefCommissariatPosition?->activeAssignment?->employee;
    }

    /**
     * Сотрудники отдела (через назначения)
     */
    public function getEmployeesAttribute()
    {
        return Employee::whereHas('employeePositions.commissariatPosition', function ($q) {
            $q->where('commissariat_positions.department_id', $this->id);
        })
        ->with('person')
        ->get()
        ->unique('id');
    }
}