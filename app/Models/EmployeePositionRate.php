<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeePositionRate extends Model
{
    protected $table = 'employee_position_rates';

    protected $fillable = [
        'rate',
    ];

    /**
     * ✅ Обратная связь: все назначения сотрудников с этим rate
     */
    public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }
}
