<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Commissariat extends Model
{
    protected $table = 'commissariats';

    protected $fillable = [
        'name',
        'longitude',
        'latitude',
    ];

    public function chiefPosition(): HasOne
    {
        return $this->hasOne(EmployeePosition::class)
            ->whereHas('position.chiefType', function ($query) {
                $query->where('name', 'начальник комиссариата');
            })
            ->with(['employee', 'position']);
    }

    // получить начальника через поле chief_employee_id
    // public function chiefEmployee()
    // {
    //     return $this->belongsTo(Employee::class, 'chief_employee_id');
    // }

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

    // тут потом поправить сделать связь через EmployeePosition

    // сотрудники прямо зависящие от комиссариата
    // public function employeesNotIndependent()
    // {
    //     // сотрудники, назначенные на позиции данного комиссариата,
    //     // где позиция в commissariats_positions без department/division и is_independent = 0
    //     return Employee::whereHas('employeePositions', function ($q) {
    //         $q->where('is_independent', 0)
    //             ->whereHas('commissariatPosition', function ($q2) {
    //                 $q2->where('commissariat_id', $this->id)
    //                     ->whereNull('department_id')
    //                     ->whereNull('division_id');
    //             });
    //     })->with(['employeePositions' => function ($q) {
    //         $q->where('is_independent', 0)
    //             ->whereHas('commissariatPosition', function ($q2) {
    //                 $q2->where('commissariat_id', $this->id)
    //                     ->whereNull('department_id')
    //                     ->whereNull('division_id');
    //             })->with('position');
    //     }])->get();
    // }

    // сотрудники самостоятельные
    // public function employeesIndependent()
    // {
    //     return Employee::whereHas('employeePositions', function ($q) {
    //         $q->where('is_independent', 1)
    //             ->whereHas('commissariatPosition', function ($q2) {
    //                 $q2->where('commissariat_id', $this->id)
    //                     ->whereNull('department_id')
    //                     ->whereNull('division_id');
    //             });
    //     })->with(['employeePositions' => function ($q) {
    //         $q->where('is_independent', 1)
    //             ->whereHas('commissariatPosition', function ($q2) {
    //                 $q2->where('commissariat_id', $this->id)
    //                     ->whereNull('department_id')
    //                     ->whereNull('division_id');
    //             })->with('position');
    //     }])->get();
    // }

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
