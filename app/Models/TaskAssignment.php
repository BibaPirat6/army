<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'employee_id', 'quota', 'priority',
        'start_date', 'end_date', 'completed_count'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'priority' => 'integer',
        'quota' => 'integer',
        'completed_count' => 'integer',
    ];

    // Задача-родитель
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Сотрудник, которому назначено
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Журнал выполнений
    // public function completions()
    // {
    //     return $this->hasMany(TaskCompletion::class);
    // }

    // Оставшаяся квота (вычисляемое)
    public function getRemainingQuotaAttribute(): int
    {
        return max(0, $this->quota - $this->completed_count);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            1 => 'Высокий',
            2 => 'Средний',
            3 => 'Низкий',
            default => 'Не указан',
        };
    }

    public function getCurrentDayQuotaAttribute(): int
    {
        if (! $this->relationLoaded('task')) {
            $this->load('task');
        }

        return (int) ($this->task?->taskInstances()
            ->whereDate('date', now()->toDateString())
            ->value('daily_quota') ?? 0);
    }
}