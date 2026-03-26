<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissariatPosition extends Model
{
    //
    protected $table = 'commissariats_positions';

    protected $fillable = [
        'commissariat_id',
        'department_id',
        'division_id',
        'position_id',
        'rate_total',
    ];

    // 🔗 Отношения

    /**
     * Комиссариат, к которому привязана должность
     */
    public function commissariat(): BelongsTo
    {
        return $this->belongsTo(Commissariat::class);
    }

    /**
     * Отдел (опционально)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Отделение (опционально)
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

       /**
     * Тип должности (например, "Менеджер", "Начальник отдела")
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
