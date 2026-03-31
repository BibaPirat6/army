<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Employee;
use Illuminate\Support\Collection;

class Commissariat extends Model
{
    protected $table = 'commissariats';

    protected $fillable = [
        'name',
        'longitude',
        'latitude',
    ];

    // получить employee_position, который соответствует должности начальника комиссариата
    public function chiefPosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class)
            ->whereHas('position.chiefType', function ($query) {
                $query->where('name', 'начальник комиссариата');
            })
            ->with(['employee', 'position']);
    }

    // получаем весь штат сотрудников комиссариата
    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }

    /**
     * Получить все отделы комиссариата
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Получить все отделения комиссариата
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Сотрудники, зависимые от комиссариата (is_independent = 0),
     * назначенные на позиции данного комиссариата без department и division.
     * Возвращает коллекцию Employee с подгруженными соответствующими employeePositions->position.
     */
    public function employeesNotIndependent(): Collection
    {
        return Employee::whereHas('employeePositions', function ($q) {
            $q->where('commissariat_id', $this->id)
                ->where('is_independent', 0)
                ->whereNull('department_id')
                ->whereNull('division_id');
        })->with(['employeePositions' => function ($q) {
            $q->where('commissariat_id', $this->id)
                ->where('is_independent', 0)
                ->whereNull('department_id')
                ->whereNull('division_id')
                ->with('position');
        }, 'person'])->get();
    }

    /**
     * Самостоятельные сотрудники комиссариата (is_independent = 1),
     * назначенные на позиции данного комиссариата без department и division.
     */
    public function employeesIndependent(): Collection
    {
        return Employee::whereHas('employeePositions', function ($q) {
            $q->where('commissariat_id', $this->id)
                ->where('is_independent', 1)
                ->whereNull('department_id')
                ->whereNull('division_id');
        })->with(['employeePositions' => function ($q) {
            $q->where('commissariat_id', $this->id)
                ->where('is_independent', 1)
                ->whereNull('department_id')
                ->whereNull('division_id')
                ->with('position');
        }, 'person'])->get();
    }

    // самостоятельные отделения
    public function divisionsIntependent()
    {
        return $this->divisions()->whereNull('department_id')->get();
    }

    // АКСЕССОРЫ
    public function getChiefAttribute()
    {
        return $this->chiefPosition?->employee;
    }
}
