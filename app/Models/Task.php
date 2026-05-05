<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'color', 'quota',
        'employee_position_id',
        'start_date', 'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Кто создал задачу
    // public function creator()
    // {
    //     return $this->belongsTo(User::class, 'created_by');
    // }

    // Ответственный (должность)
    public function employeePosition()
    {
        return $this->belongsTo(EmployeePosition::class);
    }

    // Подразделение через должность
    public function getUnitAttribute()
    {
        $position = $this->employeePosition;
        if (! $position || ! $position->commissariatPosition) {
            return null;
        }

        return $position->commissariatPosition->division
            ?? $position->commissariatPosition->department
            ?? $position->commissariatPosition->commissariat;
    }

    // Подзадачи
    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    // Назначения сотрудникам
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    // Файлы задачи
    public function files()
    {
        return $this->hasMany(TaskFile::class);
    }

    public function taskInstance()
    {
        return $this->belongsTo(TaskInstance::class);
    }

    // Суммарные временные оценки (вычисляются на основе subtasks)
    public function getTotalMinTimeAttribute(): int
    {
        return $this->subtasks->sum('min_time_minutes');
    }

    public function getTotalAvgTimeAttribute(): int
    {
        return $this->subtasks->sum('avg_time_minutes');
    }

    public function getTotalMaxTimeAttribute(): int
    {
        return $this->subtasks->sum('max_time_minutes');
    }
}
