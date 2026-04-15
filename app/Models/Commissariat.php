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
     * Возвращает Employee только если он активен и не находится в отсутствии.
     */
    public function getChiefAttribute()
    {
        // 1. Получаем штатную должность начальника
        $position = $this->chiefCommissariatPosition;

        if (! $position) {
            return null;
        }

        // 2. Получаем текущее активное назначение
        $assignment = $position->activeAssignment;

        if (! $assignment || ! $assignment->employee) {
            return null;
        }

        // 3. Проверяем статус назначения
        // IDs статусов, при которых начальник НЕ должен отображаться как действующий
        // Замените [2, 3, 4] на реальные ID из вашей таблицы employee_position_statuses
        // Например: 2=Отпуск, 3=Декрет, 4=Уволен
        $excludedStatusIds = [2, 3, 4];

        // Если статус назначения попадает в исключенные — возвращаем null
        if (in_array($assignment->employee_position_status_id, $excludedStatusIds)) {
            return null;
        }

        // Также можно проверить дату окончания, если она есть и уже прошла
        if ($assignment->ended_at && \Carbon\Carbon::parse($assignment->ended_at)->isPast()) {
            return null;
        }

        // Если все проверки пройдены — возвращаем сотрудника
        return $assignment->employee;
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
