<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Commissariat extends Model
{
    protected $table = 'commissariats';

    protected $fillable = [
        'name',
        'chief_employee_id',
    ];

    /**
     * Получить начальника комиссариата
     */
    public function chiefEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'chief_employee_id');
    }


    // вывод с EmployeePosition начальника комиссариата для того чтобы если мы сняли с должности начальника то в таблице комиссариатов выводилос что нет начальника

    public function chiefEmployeePosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class, 'commissariat_id')
            ->whereHas('position', function ($query) {
                $query->where('name', 'Начальник комиссариата');
            })
            ->with('employee.person');
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
     * Получить всех сотрудников комиссариата через должности
     */
    public function employees()
    {
        return Employee::whereHas('positions', function ($query) {
            $query->where('commissariat_id', $this->id);
        })->get();
    }

    // сотрудники прямо зависящие от комиссариата
    public function employeesNotIndependent()
    {
        return Employee::whereHas('employeePositions', function ($query) {
            $query->where('commissariat_id', $this->id)
                ->where('is_independent', 0)
                ->whereNull('department_id')
                ->whereNull('division_id');
        })
            ->with([
                'employeePositions' => function ($query) {
                    $query->where('commissariat_id', $this->id)
                        ->where('is_independent', 0)
                        ->whereNull('department_id')
                        ->whereNull('division_id')
                        ->with('position');
                }
            ])
            ->get();
    }

    // сотрудники самостоятельные
    public function employeesIndependent()
    {
        return Employee::whereHas('employeePositions', function ($query) {
            $query->where('commissariat_id', $this->id)
                ->where('is_independent', 1)
                ->whereNull('department_id')
                ->whereNull('division_id');
        })
            ->with([
                'employeePositions' => function ($query) {
                    $query->where('commissariat_id', $this->id)
                        ->where('is_independent', 1)
                        ->whereNull('department_id')
                        ->whereNull('division_id')
                        ->with('position');
                }
            ])
            ->get();
    }


    // самостоятельные отделения
    public function divisionsIntependent()
    {
        return $this->divisions()->whereNull('department_id')->get();
    }
}
