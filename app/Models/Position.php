<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = [
        'name',
        'position_type_id',
    ];

    public function positionType()
    {
        return $this->belongsTo(PositionType::class, 'position_type_id');
    }

    public function employeePositions()
    {
        return $this->hasMany(EmployeePosition::class, 'position_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_positions')
            ->withPivot('rate')
            ->withTimestamps();
    }
}
