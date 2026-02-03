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
        "phones",
        "emails",
        "photo",
    ];
    protected $casts = [
        'phones' => 'array',
        'emails' => 'array',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
