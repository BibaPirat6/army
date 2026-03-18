<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'persons';

    protected $guarded = [];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
