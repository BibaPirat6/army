<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePositionStatus extends Model
{
    //
    protected $table = 'commissariats_positions';

    protected $fillable = [
        'name',
    ];

}
