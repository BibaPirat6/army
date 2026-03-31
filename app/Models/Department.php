<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'commissariat_id',
    ];

    // получить employee_position, который соответствует должности начальника отдела
    public function chiefPosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class)
            ->whereHas('position.chiefType', function ($query) {
                $query->where('name', 'начальник отдела');
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
     * Получить комиссариат, к которому относится отдел
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Получить все отделения отдела
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }
}
