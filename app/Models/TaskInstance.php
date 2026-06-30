<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'assignment_id',
        'date',
        'daily_quota',
    ];

    protected $casts = [
        'date' => 'date',
        'daily_quota' => 'integer',
    ];

    // Родительская задача
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'assignment_id');
    }

    // Является ли экземпляр выполненным (проверка через назначения)
    public function getIsCompletedAttribute(): bool
    {
        return $this->taskAssignments()
            ->where('status', '!=', 'completed')
            ->doesntExist();
    }

    // Назначения сотрудникам на этот день
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_instance_id');
    }
}
