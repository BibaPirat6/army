<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = ['name', 'general_min_minutes',  'general_avg_minutes',  'general_max_minutes', 'quota'];

    protected $casts = [
        'general_min_minutes' => 'decimal:2',
        'general_avg_minutes' => 'decimal:2',
        'general_max_minutes' => 'decimal:2',
        'quota' => 'integer',
    ];
}
   