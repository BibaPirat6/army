<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'commissariat_id',
        'chief_employee_id',
    ];

    /**
     * Получить комиссариат, к которому относится отдел
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Получить начальника отдела
     */
    public function chiefEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'chief_employee_id');
    }

    /**
     * Получить все отделения отдела
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Получить всех сотрудников отдела через должности
     */
    public function employees()
    {
        return Employee::whereHas('positions', function ($query) {
            $query->where('department_id', $this->id);
        })->get();
    }
}
