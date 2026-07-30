<?php

namespace App\Models;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommissariatPosition extends Model
{
    protected $table = 'commissariat_positions';

    protected $fillable = [
        'commissariat_id',
        'department_id',
        'division_id',
        'position_id',
        'rate_total',  // ✅ правильно
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

    /**
     * Активное назначение (сотрудник, который сейчас работает в этой должности)
     */
    public function activeAssignment(): HasOne
    {
        return $this->hasOne(EmployeePosition::class, 'commissariat_position_id')
            ->where('employee_position_status_id', 1);
    }

    /**
     * Получить занятые ставки (из уже загруженных отношений)
     */
    public function getOccupiedRateAttribute(): float
    {
        if (!$this->relationLoaded('employeePositions')) {
            return 0;
        }
        
        return (float) $this->employeePositions
            ->filter(function ($ep) {
                return $ep->employeePositionStatus && $ep->employeePositionStatus->occupies_rate;
            })
            ->sum('rate');
    }

    /**
     * Получить свободные ставки
     */
    public function getAvailableRateAttribute(): float
    {
        return round($this->rate_total - $this->occupied_rate, 2);
    }

    /**
     * Есть ли вакансия (свободные ставки)
     */
    public function getHasVacancyAttribute(): bool
    {
        return $this->available_rate > 0;
    }

    /**
     * Полностью укомплектована
     */
    public function getIsFullyStaffedAttribute(): bool
    {
        return $this->available_rate <= 0;
    }

    /**
     * Получить процент занятости
     */
    public function getOccupancyPercentAttribute(): float
    {
        if ($this->rate_total <= 0) {
            return 0;
        }
        
        return round(($this->occupied_rate / $this->rate_total) * 100, 2);
    }

    /**
     * Scope filters
     */
    public function scopeFilter(
        Builder $query,
        BaseFilter $filter,
    ): Builder {
        return $filter->apply($query);
    }
}