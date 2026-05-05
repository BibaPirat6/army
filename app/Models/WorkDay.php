<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkDay extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'date', 'work_start', 'work_end', 'breaks', 'type'];

    protected $casts = [
        'date' => 'date',
        'breaks' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}