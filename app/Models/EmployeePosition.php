<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePosition extends Model
{
    protected $table = 'employee_positions';

    protected $fillable = [
        'employee_id',
        'commissariat_position_id',
        'employee_position_status_id',
        'is_independent',
        'rate',
    ];

    protected $casts = [
        'is_independent' => 'boolean',
    ];

    /**
     * Получить сотрудника
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Получить комиссариат-должность
     */
    public function commissariatPosition(): BelongsTo
    {
        return $this->belongsTo(CommissariatPosition::class);
    }

    /**
     * Получить статус должности сотрудника
     */
    public function employeePositionStatus(): BelongsTo
    {
        return $this->belongsTo(EmployeePositionStatus::class);
    }
}
