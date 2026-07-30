<?php

namespace App\Models;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PositionType extends Model
{
    protected $table = 'position_types';

    protected $fillable = [
        'name',
    ];

    public function positions()
    {
        return $this->hasMany(Position::class, 'position_type_id');
    }

    public function scopeFilter(
        Builder $query,
        BaseFilter $filter,
    ): Builder {
        return $filter->apply($query);
    }
}
