<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissariatPosition extends Model
{
    //
    protected $table = 'commissariats_positions';

    protected $fillable = [
        'commissariat_id',
        'department_id',
        'division_id',
        'position_id',
        'rate_total',
    ];

    // 🔗 Отношения

    /**
     * Комиссариат, к которому привязана должность
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Отдел (опционально)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Отделение (опционально)
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Тип должности (например, "Менеджер", "Начальник отдела")
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    // ✅ обратная СВЯЗЬ: все назначения сотрудников на эту должность
    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }


    // ДОПЫ

    // Сколько ставок уже занято на этой позиции (сумма employee_positions.rate)
    public function getUsedRateAttribute()
    {
        return (float) $this->employeePositions()->sum('rate');
    }

    // Сколько осталось свободных ставок на этой позиции
    public function getAvailableRateAttribute()
    {
        return (float) $this->rate_total - $this->used_rate;
    }
}
