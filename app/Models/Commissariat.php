<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Commissariat extends Model
{
    protected $table = 'commissariats';

    protected $fillable = ['name', 'longitude', 'latitude'];

    // ===== ОТНОШЕНИЯ =====

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Все штатные должности комиссариата
     */
    public function commissariatPositions(): HasMany
    {
        return $this->hasMany(CommissariatPosition::class);
    }

    /**
     * Все назначения сотрудников (через штатные должности)
     */
    public function employeePositions(): HasManyThrough
    {
        return $this->hasManyThrough(
            EmployeePosition::class,
            CommissariatPosition::class,
            'commissariat_id',
            'commissariat_position_id'
        );
    }

    /**
     * Штатная должность начальника комиссариата
     */
    public function chiefCommissariatPosition(): HasOne
    {
        return $this->hasOne(CommissariatPosition::class)
            ->whereNull('department_id')
            ->whereNull('division_id')
            ->whereHas('position.chiefType', fn ($q) => $q->where('name', 'начальник комиссариата')
            );
    }

    /**
     * Аксессор: Получает действующего начальника комиссариата.
     * Возвращает сотрудника только если он назначен со статусом "работает" (ID = 1).
     */
    public function getChiefAttribute()
    {
        // Получаем штатную должность начальника и сразу активное назначение
        // (связь activeAssignment уже фильтрует по employee_position_status_id = 1)
        return $this->chiefCommissariatPosition?->activeAssignment?->employee;
    }

    // ===== МЕТОДЫ ДЛЯ VIEW =====

    /**
     * Сотрудники на уровне комиссариата (зависимые, is_independent = false)
     */
    public function employeesNotIndependent(): Collection
    {
        return $this->getEmployeesByLevel(independent: false);
    }

    /**
     * Сотрудники на уровне комиссариата (самостоятельные, is_independent = true)
     */
    public function employeesIndependent(): Collection
    {
        return $this->getEmployeesByLevel(independent: true);
    }

    /**
     * Самостоятельные отделения (без отдела)
     */
    public function divisionsIndependent()
    {
        return $this->divisions()->whereNull('department_id');
    }

    /**
     * Приватный метод для получения сотрудников по уровню
     */
    private function getEmployeesByLevel(bool $independent): Collection
    {
        return Employee::whereHas('employeePositions.commissariatPosition', function ($q) use ($independent) {
            $q->where('commissariat_positions.commissariat_id', $this->id)
                ->where('commissariat_positions.is_independent', $independent)
                ->whereNull('commissariat_positions.department_id')
                ->whereNull('commissariat_positions.division_id');
        })
            ->with([
                'person',
                'employeePositions' => fn ($q) => $q->with([
                    'commissariatPosition.position',
                    'status',
                ]),
            ])
            ->get()
            ->unique('id');
    }

    /**
     * Статистика штата
     */
    public function getStaffStatsAttribute(): array
    {
        $total = $this->commissariatPositions()->count();
        $occupied = $this->commissariatPositions()
            ->whereHas('employeePositions', fn ($q) => $q->active()->occupiesRate())
            ->count();
        $totalRate = $this->commissariatPositions()->sum('rate_total');
        $occupiedRate = $this->employeePositions()
            ->active()
            ->occupiesRate()
            ->sum('rate');

        return [
            'total_positions' => $total,
            'occupied_positions' => $occupied,
            'vacant_positions' => $total - $occupied,
            'total_rate' => (float) $totalRate,
            'occupied_rate' => (float) $occupiedRate,
            'available_rate' => round($totalRate - $occupiedRate, 2),
        ];
    }
}
