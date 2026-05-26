<?php

namespace App\Models;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = ['name', 'commissariat_id', 'department_id'];

    // ===== ОТНОШЕНИЯ =====

    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Штатные должности в этом отделении
     */
    public function commissariatPositions(): HasMany
    {
        return $this->hasMany(CommissariatPosition::class);
    }

    /**
     * Назначения сотрудников в этом отделении
     */
    public function employeePositions(): HasManyThrough
    {
        return $this->hasManyThrough(
            EmployeePosition::class,
            CommissariatPosition::class,
            'division_id',
            'commissariat_position_id'
        );
    }

    /**
     * Штатная должность начальника отделения
     */
    public function chiefCommissariatPosition(): HasOne
    {
        return $this->hasOne(CommissariatPosition::class)
            ->whereHas('position.chiefType', fn ($q) => $q->where('name', 'начальник отделения'));
    }

    // ===== АКСЕССОРЫ =====

    public function getChiefAttribute()
    {
        return $this->chiefCommissariatPosition?->activeAssignment?->employee;
    }

    /**
     * Сотрудники отделения
     */
    public function getEmployeesAttribute()
    {
        return Employee::whereHas('employeePositions.commissariatPosition', function ($q) {
            $q->where('commissariat_positions.division_id', $this->id);
        })
            ->with('person')
            ->get()
            ->unique('id');
    }

    public function scopeFilter(
        Builder $query,
        BaseFilter $filter,
    ): Builder {
        return $filter->apply($query);
    }
}
