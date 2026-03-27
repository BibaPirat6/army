<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Commissariat extends Model
{
    protected $table = 'commissariats';

    protected $fillable = [
        'name',
        'chief_employee_id',
        'longitude',
        'latitude',
    ];

    /**
     * Обратная связь: все должности в этом отделении
     */
    public function commissariatPositions(): HasMany
    {
        return $this->hasMany(CommissariatPosition::class);
    }

    // получить начальника через поле chief_employee_id
    public function chiefEmployee()
    {
        return $this->belongsTo(Employee::class, 'chief_employee_id');
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


        // получаем все назначения должностей в нашем комиссиарате
    public function employeePositions(): HasManyThrough
    {
        // все назначения должностей в нашем комиссариате: через commissariat_positions
        return $this->hasManyThrough(
            EmployeePosition::class,
            CommissariatPosition::class,
            'commissariat_id',          // foreign key on commissariat_positions -> commissariats.id
            'commissariat_position_id', // foreign key on employee_positions -> commissariats_positions.id
            'id',
            'id'
        );
    }


    /**
     * Получить всех сотрудников комиссариата через должности
     */
    public function employees()
    {
        // возвращаем сотрудников, у которых есть EmployeePosition,
        // связанная с CommissariatPosition этого комиссариата
        return Employee::whereHas('employeePositions', function ($q) {
            $q->whereHas('commissariatPosition', function ($q2) {
                $q2->where('commissariat_id', $this->id);
            });
        })->get();
    }

    // сотрудники прямо зависящие от комиссариата
    public function employeesNotIndependent()
    {
        // сотрудники, назначенные на позиции данного комиссариата,
        // где позиция в commissariats_positions без department/division и is_independent = 0
        return Employee::whereHas('employeePositions', function ($q) {
            $q->where('is_independent', 0)
                ->whereHas('commissariatPosition', function ($q2) {
                    $q2->where('commissariat_id', $this->id)
                        ->whereNull('department_id')
                        ->whereNull('division_id');
                });
        })->with(['employeePositions' => function ($q) {
            $q->where('is_independent', 0)
                ->whereHas('commissariatPosition', function ($q2) {
                    $q2->where('commissariat_id', $this->id)
                        ->whereNull('department_id')
                        ->whereNull('division_id');
                })->with('position');
        }])->get();
    }

    // сотрудники самостоятельные
    public function employeesIndependent()
    {
        return Employee::whereHas('employeePositions', function ($q) {
            $q->where('is_independent', 1)
                ->whereHas('commissariatPosition', function ($q2) {
                    $q2->where('commissariat_id', $this->id)
                        ->whereNull('department_id')
                        ->whereNull('division_id');
                });
        })->with(['employeePositions' => function ($q) {
            $q->where('is_independent', 1)
                ->whereHas('commissariatPosition', function ($q2) {
                    $q2->where('commissariat_id', $this->id)
                        ->whereNull('department_id')
                        ->whereNull('division_id');
                })->with('position');
        }])->get();
    }

    // самостоятельные отделения
    public function divisionsIntependent()
    {
        return $this->divisions()->whereNull('department_id')->get();
    }


    // вывод сколько каких должностей в каждом комиссариате
    public function positionsCommissariat()
    {
        return $this->employeePositions()
            ->with('position')
            ->get()
            ->pluck('position')
            ->filter()
            ->unique('id');
    }
}
