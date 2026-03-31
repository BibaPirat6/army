<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'name',
        'commissariat_id',
        'department_id',
    ];

    // получить employee_position, который соответствует должности начальника отделения
    public function chiefPosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class)
            ->whereHas('position.chiefType', function ($query) {
                $query->where('name', 'начальник отделения');
            })
            ->with(['employee', 'position']);
    }

    // аксессор
    public function getChiefAttribute()
    {
        return $this->chiefPosition?->employee;
    }

    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }

    /**
     * Получить комиссариат, к которому относится отделение
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Получить отдел, к которому относится отделение
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
