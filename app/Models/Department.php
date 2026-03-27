<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'commissariat_id',
        'chief_employee_id',
    ];

    // получить начальника через поле chief_employee_id
    public function chiefEmployee()
    {
        return $this->belongsTo(Employee::class, 'chief_employee_id');
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
