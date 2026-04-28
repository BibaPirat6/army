<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'color', 'quota',
        'commissariat_id', 'department_id', 'division_id',
        'created_by', 'start_date', 'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Кто создал задачу
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Подразделение, к которому привязана задача (один из трёх вариантов)
    public function commissariat()
    {
        return $this->belongsTo(Commissariat::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    // Универсальный метод для получения объекта подразделения
    public function getUnitAttribute()
    {
        return $this->commissariat ?? $this->department ?? $this->division;
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
