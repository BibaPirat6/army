<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiefType extends Model
{
    protected $table = 'chief_types';

    protected $fillable = [
        'name',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class, 'position_type_id');
    }
}
