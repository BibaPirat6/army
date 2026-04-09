<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissariatPosition extends Model
{
    protected $table = 'commissariat_positions';

    protected $fillable = [
        'commissariat_id',
        'department_id',
        'division_id',
        'position_id',
        'rate_total',
        'is_independent',
    ];

    protected $casts = [
        'rate_total' => 'decimal:2',
        'is_independent' => 'boolean',
    ];

    /**
     * Штатная должность принадлежит комиссариату
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Штатная должность может принадлежать отделу
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Штатная должность может принадлежать отделению
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Штатная должность ссылается на справочник должностей
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Назначения сотрудников на эту штатную должность
     */
    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class, 'commissariat_position_id');
    }

    // ДОП ПЛЮШКИ

    /**
     * Активное назначение (работает прямо сейчас)
     */
    public function activeEmployeePosition()
    {
        return $this->hasOne(EmployeePosition::class, 'commissariat_position_id')
            ->where('is_active', true)
            ->whereNull('ended_at')
            ->with(['employee.person', 'status']);
    }

    /**
     * Проверка: занята ли должность (есть активное назначение со статусом "работает")
     */
    public function getIsOccupiedAttribute(): bool
    {
        return $this->activeEmployeePosition()
            ->whereHas('status', fn ($q) => $q->where('name', 'работает'))
            ->exists();
    }

    /**
     * Свободная ставка: rate_total минус сумма занятых ставок
     */
    public function getAvailableRateAttribute(): float
    {
        $occupied = $this->employeePositions()
            ->where('is_active', true)
            ->whereNull('ended_at')
            ->whereHas('status', fn ($q) => $q->where('occupies_rate', true))
            ->sum('rate');

        return max(0, $this->rate_total - $occupied);
    }
}
