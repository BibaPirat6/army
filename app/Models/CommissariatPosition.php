<?php

namespace App\Models;

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
     * ✅ Активное назначение (сотрудник, который сейчас работает в этой должности)
     * Проверяем, что статус назначения = 1 ("работает")
     */
    public function activeAssignment(): HasOne
    {
        return $this->hasOne(EmployeePosition::class, 'commissariat_position_id')   
            ->where('employee_position_status_id', 1); // 🔥 ID статуса "работает"
    }
}
