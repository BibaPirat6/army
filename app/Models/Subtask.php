<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'title', 'min_time_minutes', 'avg_time_minutes', 'max_time_minutes'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}