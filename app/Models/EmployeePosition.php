<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class EmployeePosition extends Model
{
    protected $table = 'employee_positions';

    protected $fillable = [
        'employee_id',
        'commissariat_position_id',
        'rate',
        'employee_position_status_id',
        'started_at',
        'ended_at',
        'is_active',
        'expected_return_at',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Сотрудник
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Назначение ссылается на штатную должность (КЛЮЧЕВАЯ СВЯЗЬ)
     */
    public function commissariatPosition(): BelongsTo
    {
        return $this->belongsTo(CommissariatPosition::class, 'commissariat_position_id');
    }

    /**
     * Статус
     */
    public function employeePositionStatus(): BelongsTo
    {
        return $this->belongsTo(EmployeePositionStatus::class);
    }

    // ===== СКВОЗНЫЕ ОТНОШЕНИЯ (через commissariat_position) =====

    /**
     * Комиссариат (через штатную должность)
     */
    public function commissariat(): HasOneThrough
    {
        return $this->hasOneThrough(
            Commissariat::class,
            CommissariatPosition::class,
            'id', // FK на commissariat_positions
            'id', // FK на commissariats
            'commissariat_position_id', // FK на employee_positions
            'commissariat_id' // FK на commissariat_positions
        );
    }

    /**
     * Отдел (через штатную должность)
     */
    public function department(): HasOneThrough
    {
        return $this->hasOneThrough(
            Department::class,
            CommissariatPosition::class,
            'id',
            'id',
            'commissariat_position_id',
            'department_id'
        );
    }

    /**
     * Отделение (через штатную должность)
     */
    public function division(): HasOneThrough
    {
        return $this->hasOneThrough(
            Division::class,
            CommissariatPosition::class,
            'id',
            'id',
            'commissariat_position_id',
            'division_id'
        );
    }

    /**
     * Должность из справочника (через штатную должность)
     */
    public function position(): HasOneThrough
    {
        return $this->hasOneThrough(
            Position::class,
            CommissariatPosition::class,
            'id',
            'id',
            'commissariat_position_id',
            'position_id'
        );
    }

    // АКСЕССОРЫ

    public function getStatusNameAttribute()
    {
        return $this->employeePositionStatus?->name;
    }
}
