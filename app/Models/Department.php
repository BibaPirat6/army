<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'commissariat_id',
        'chief_employee_id',
    ];

    // получить начальника
    public function chiefEmployeePosition(): HasOneThrough
    {
        // найти EmployeePosition через CommissariatPosition, где department_id = $this->id
        return $this->hasOneThrough(
            EmployeePosition::class,
            CommissariatPosition::class,
            'department_id',            // foreign key on commissariat_positions -> departments.id
            'commissariat_position_id', // foreign key on employee_positions -> commissariats_positions.id
            'id',
            'id'
        )
        ->whereHas('position.chiefType', function ($query) {
            $query->where('name', 'начальник отдела');
        })
        // ограничить также по commissariat, чтобы не цеплялись позиции из других комиссариатов
        ->where('commissariat_positions.commissariat_id', $this->commissariat_id)
        ->with('employee.person');
    }

    /**
     *  Все должности в этом отделе (в рамках комиссариата)
     */
    public function commissariatPositions(): HasMany
    {
        return $this->hasMany(CommissariatPosition::class);
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

    /**
     * Получить всех сотрудников отдела через должности
     */
    public function employees()
    {
        return Employee::whereHas('employeePositions', function ($q) {
            $q->whereHas('commissariatPosition', function ($q2) {
                $q2->where('department_id', $this->id);
            });
        })->get();
    }
}
