<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    protected $table = 'subtasks';

    protected $fillable = ['name', 'min_minutes', 'avg_minutes', 'max_minutes', 'task_id'];

    protected $casts = [
        'min_minutes' => 'decimal:2',
        'avg_minutes' => 'decimal:2',
        'max_minutes' => 'decimal:2',
    ];
}
