<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;
    protected $table = "persons";

    protected $fillable = [
        "first_name",
        "last_name",
        "patronymic",
        "phone",
        "email",
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
