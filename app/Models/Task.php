<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'end_date' => 'date',
        'files' => 'array',
        'quota' => 'integer',
    ];

    // Связи
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

    // Агрегаты подзадач
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

    public function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) return '0 мин';
        $h = floor($minutes / 60);
        $m = $minutes % 60;
        if ($h > 0) return "{$h}ч {$m}м";
        return "{$m} мин";
    }

    /*
    |--------------------------------------------------------------------------
    | РАБОТА С ФАЙЛАМИ
    |--------------------------------------------------------------------------
    */

    /**
     * Получить список файлов
     */
    public function getFilesList(): array
    {
        if (empty($this->files)) {
            return [];
        }
        
        if (is_string($this->files)) {
            $decoded = json_decode($this->files, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        if (is_array($this->files)) {
            return $this->files;
        }
        
        return [];
    }

    /**
     * Добавить файл
     */
    public function addFile($uploadedFile): ?array
    {
        try {
            $files = $this->getFilesList();
            
            $path = $uploadedFile->store('tasks/' . $this->id, 'public');
            
            $fileData = [
                'id' => (string) Str::uuid(),
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
        } catch (\Exception $e) {
            \Log::error('Ошибка добавления файла: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Удалить файл по ID
     */
    public function removeFile(string $fileId): bool
    {
        try {
            $files = $this->getFilesList();
            $found = false;
            
            foreach ($files as $key => $file) {
                if (isset($file['id']) && $file['id'] === $fileId) {
                    // Удаляем физический файл
                    if (isset($file['path']) && Storage::disk('public')->exists($file['path'])) {
                        Storage::disk('public')->delete($file['path']);
                    }
                    unset($files[$key]);
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $this->files = array_values($files);
                $this->save();
            }
            
            return $found;
        } catch (\Exception $e) {
            \Log::error('Ошибка удаления файла: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить файлы с URL
     */
    public function getFilesWithUrls(): array
    {
        $files = $this->getFilesList();
        
        foreach ($files as &$file) {
            if (isset($file['path'])) {
                $file['url'] = Storage::url($file['path']);
            }
        }
        
        return $files;
    }
}