<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkStatus extends Model
{
    protected $table = 'work_statuses';

    protected $fillable = [
        'name',
        'description',
    ];


    public function employees()
    {
        return $this->hasMany(Employee::class, 'work_status_id');
    }
}
