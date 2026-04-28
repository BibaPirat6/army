<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskFile extends Model
{
    protected $fillable = [
        'task_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}