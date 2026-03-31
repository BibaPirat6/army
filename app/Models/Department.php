<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'commissariat_id',
    ];

     public function employeePositions(): HasMany
    {
        return $this->hasMany(EmployeePosition::class);
    }

    /**
     * Получить комиссариат, к которому относится отдел
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Получить все отделения отдела
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }
}
