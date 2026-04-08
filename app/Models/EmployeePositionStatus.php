<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeePositionStatus extends Model
{
    protected $table = 'employee_position_statuses';

    protected $fillable = [
        'name',
        'color'
    ];

    /**
     * ✅ Обратная связь: все назначения сотрудников с этим статусом
     */
    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }
}
