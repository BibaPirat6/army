<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'name',
        'commissariat_id',
        'department_id',
        'chief_employee_id',
    ];

    /**
     * ✅ Все должности в этом отделении
     */
    public function commissariatPositions(): HasMany
    {
        return $this->hasMany(CommissariatPosition::class);
    }

    // получить начальника отделения через CommissariatPosition -> EmployeePosition
    public function chiefEmployeePosition(): HasOneThrough
    {
        return $this->hasOneThrough(
            EmployeePosition::class,
            CommissariatPosition::class,
            'division_id',              // foreign key on commissariat_positions -> divisions.id
            'commissariat_position_id', // foreign key on employee_positions -> commissariats_positions.id
            'id',
            'id'
        )
        ->whereHas('position.chiefType', function ($query) {
            $query->where('name', 'начальник отделения');
        })
        ->where('commissariat_positions.commissariat_id', $this->commissariat_id)
        ->with('employee.person');
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

    public function employees()
    {
        return Employee::whereHas('employeePositions', function ($q) {
            $q->whereHas('commissariatPosition', function ($q2) {
                $q2->where('division_id', $this->id);
            });
        })->get();
    }
}
