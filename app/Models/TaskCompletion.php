<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_assignment_id', 'completed_at', 'duration_minutes'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function taskAssignment()
    {
        return $this->belongsTo(TaskAssignment::class);
    }
}