<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Storage;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'color',
        'quota',
        'employee_position_id',
        'start_date',
        'end_date',
          'files',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
         'files' => 'array',
         'quota' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function employeePosition()
    {
        return $this->belongsTo(EmployeePosition::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function taskInstances()
    {
        return $this->hasMany(TaskInstance::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Unit accessor
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Aggregates (оптимизировано)
    |--------------------------------------------------------------------------
    */

    /**
     * Получаем агрегаты одним SQL-запросом
     */
    public function getSubtasksAggregateAttribute(): object
    {
        return $this->subtasks()
            ->selectRaw('
                COALESCE(SUM(min_time_minutes), 0) as min,
                COALESCE(SUM(avg_time_minutes), 0) as avg,
                COALESCE(SUM(max_time_minutes), 0) as max
            ')
            ->first();
    }

    public function getTotalMinTimeAttribute(): int
    {
        return $this->subtasks_aggregate->min ?? 0;
    }

    public function getTotalAvgTimeAttribute(): int
    {
        return $this->subtasks_aggregate->avg ?? 0;
    }

    public function getTotalMaxTimeAttribute(): int
    {
        return $this->subtasks_aggregate->max ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Итоговое время с учетом quota
    |--------------------------------------------------------------------------
    */

    public function getTotalMinTimeWithQuotaAttribute(): int
    {
        return $this->total_min_time * ($this->quota ?? 0);
    }

    public function getTotalAvgTimeWithQuotaAttribute(): int
    {
        return $this->total_avg_time * ($this->quota ?? 0);
    }

    public function getTotalMaxTimeWithQuotaAttribute(): int
    {
        return $this->total_max_time * ($this->quota ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Форматирование (для UI)
    |--------------------------------------------------------------------------
    */

    public function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) return '0 мин';

        $h = floor($minutes / 60);
        $m = $minutes % 60;

        if ($h > 0) {
            return "{$h}ч {$m}м";
        }

        return "{$m} мин";
    }




    /**
     * Добавить файл к задаче
     */
    public function addFile($uploadedFile): array
    {
        $files = $this->files ?? [];
        
        $path = $uploadedFile->store('tasks/' . $this->id, 'public');
        
        $fileData = [
            'id' => uniqid(),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'created_at' => now()->toDateTimeString(),
        ];
        
        $files[] = $fileData;
        $this->files = $files;
        $this->save();
        
        return $fileData;
    }

    /**
     * Удалить файл из задачи
     */
    public function removeFile(string $fileId): bool
    {
        $files = $this->files ?? [];
        
        foreach ($files as $key => $file) {
            if ($file['id'] === $fileId) {
                // Удаляем физический файл
                if (Storage::disk('public')->exists($file['path'])) {
                    Storage::disk('public')->delete($file['path']);
                }
                // Удаляем из массива
                unset($files[$key]);
                $this->files = array_values($files);
                $this->save();
                return true;
            }
        }
        
        return false;
    }

    /**
     * Получить все файлы задачи
     */
    public function getFiles(): array
    {
        return $this->files ?? [];
    }

    /**
     * Получить файлы с публичными URL
     */
    public function getFilesWithUrls(): array
    {
        $files = $this->files ?? [];
        
        foreach ($files as &$file) {
            $file['url'] = Storage::url($file['path']);
        }
        
        return $files;
    }
}