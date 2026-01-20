<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'person_id',
        'role',
        'work_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function workStatus()
    {
        return $this->belongsTo(WorkStatus::class, 'work_status_id');
    }

    // public function positions()
    // {
    //     return $this->hasMany(EmployeePosition::class);
    // }

}
