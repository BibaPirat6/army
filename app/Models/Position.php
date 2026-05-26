<?php

namespace App\Models;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = [
        'name',
        'position_type_id',
        'chief_type_id',
    ];

    public function positionType()
    {
        return $this->belongsTo(PositionType::class, 'position_type_id');
    }

    public function chiefType()
    {
        return $this->belongsTo(ChiefType::class, 'chief_type_id');
    }

    public function employeePositions()
    {
        return $this->hasMany(EmployeePosition::class);
    }

    /**
     * ✅ Штатные должности, использующие эту должность из справочника
     */
    public function commissariatPositions(): HasMany
    {
        return $this->hasMany(CommissariatPosition::class, 'position_id');
    }

    // Аксессор: имя chief_type через связь
    public function getChiefTypeNameAttribute(): string
    {
        return $this->chiefType?->name ?? '❌';
    }

    // Аксессор: имя positionType через связь
    public function getPositionTypeNameAttribute(): string
    {
        return $this->positionType?->name ?? '❌';
    }

    public function scopeFilter(
        Builder $query,
        BaseFilter $filter,
    ): Builder {
        return $filter->apply($query);
    }
}
