<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'commissariat_id',
        'chief_employee_id',
    ];

    /**
     * Получить комиссариат, к которому относится отдел
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Получить начальника отдела
     */
    public function chiefEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'chief_employee_id');
    }



    public function chiefEmployeePosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class, 'employee_id', 'chief_employee_id')
            ->where('commissariat_id', $this->commissariat_id)
            ->where('department_id', $this->id)
            ->whereHas('position', function ($query) {
                $query->where('name', 'Начальник отдела');
            })
            ->with('employee.person');
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
        return Employee::whereHas('positions', function ($query) {
            $query->where('department_id', $this->id);
        })->get();
    }
}
