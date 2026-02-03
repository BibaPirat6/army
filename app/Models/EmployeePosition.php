<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePosition extends Model
{
    protected $table = 'employee_positions';

    protected $fillable = [
        'employee_id',
        'position_id',
        'commissariat_id',
        'department_id',
        'division_id',
        "is_independent",
        'rate',
    ];

    protected $casts = [
        "is_independent" => "boolean",
        "rate" => "decimal:2",
    ];

    /**
     * Получить сотрудника
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Получить должность
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Получить комиссариат
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Получить отдел
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Получить отделение
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}
