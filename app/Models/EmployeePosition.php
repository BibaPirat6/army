<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePosition extends Model
{
    protected $table = 'employee_positions';

    protected $fillable = [
        'employee_id',
        'commissariat_id',
        'department_id',
        'division_id',
        'position_id',
        'employee_position_rate_id',
        'employee_position_status_id',
        'is_independent',
    ];

    protected $casts = [
        'is_independent' => 'boolean',
    ];

    /**
     * Сотрудник
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Комиссариат
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Отдел
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Дивизия
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Должность
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Статус
     */
    public function employeePositionStatus(): BelongsTo
    {
        return $this->belongsTo(EmployeePositionStatus::class);
    }

    /**
     * Ставка
     */
    public function employeePositionRate(): BelongsTo
    {
        return $this->belongsTo(EmployeePositionRate::class);
    }

    // АКСЕССОРЫ
    public function getRateValueAttribute()
    {
        return $this->employeePositionRate?->rate;
    }

    public function getStatusNameAttribute()
    {
        return $this->employeePositionStatus?->name;
    }
}
