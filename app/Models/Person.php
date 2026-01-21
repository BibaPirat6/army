<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = "persons";

    protected $fillable = [
        "first_name",
        "last_name",
        "patronymic",
        "phone",
        "email",
        "photo",
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
