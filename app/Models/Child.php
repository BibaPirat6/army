<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    protected $fillable = [
        'person_id', 'имя', 'фамилия', 'отчество',
        'дата_рождения', 'пол',
    ];

    protected $casts = [
        'дата_рождения' => 'date',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
