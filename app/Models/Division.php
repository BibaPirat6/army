<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'name',
        'commissariat_id',
        'department_id',
        'chief_employee_id',
    ];

    // получить начальника через поле chief_employee_id
    // public function chiefEmployee()
    // {
    //     return $this->belongsTo(Employee::class, 'chief_employee_id');
    // }

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
