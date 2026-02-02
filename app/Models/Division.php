<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Получить начальника отделения
     */
    public function chiefEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'chief_employee_id');
    }

    public function chiefEmployeePosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class, 'employee_id', 'chief_employee_id')
            ->where('commissariat_id', $this->commissariat_id)
            ->where('division_id', $this->id)
            ->whereHas('position', function ($query) {
                $query->where('name', 'Начальник отделения');
            })
            ->with('employee.person');
    }

    /**
     * Получить всех сотрудников отделения через должности
     */
    // public function employees()
    // {
    //     return Employee::whereHas('positions', function ($query) {
    //         $query->where('division_id', $this->id);
    //     })->get();
    // }
    public function employees()
    {
        return $this->belongsToMany(
            Employee::class,
            'employee_positions',
            'division_id',
            'employee_id'
        );
    }
}
