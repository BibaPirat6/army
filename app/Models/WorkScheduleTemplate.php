<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkScheduleTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'pattern', 'is_default'];

    protected $casts = [
        'pattern' => 'array',
        'is_default' => 'boolean',
    ];
}