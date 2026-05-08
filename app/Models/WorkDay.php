<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'date', 'work_start', 'work_end',
        'breaks', 'type', 'weekly_hours', 'daily_hours_target',
    ];

    protected $casts = [
        'date' => 'date',
        'breaks' => 'array',
        'weekly_hours' => 'integer',
        'daily_hours_target' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalMinutesAttribute(): int
    {
        if (! $this->work_start || ! $this->work_end) {
            return 0;
        }

        [$sh, $sm] = explode(':', $this->work_start);
        [$eh, $em] = explode(':', $this->work_end);
        $total = ($eh * 60 + $em) - ($sh * 60 + $sm);

        // На случай, если breaks пришёл как строка JSON
        $breaks = $this->breaks;

        if (is_string($breaks)) {
            $breaks = json_decode($breaks, true);
        }

        foreach ((array) $breaks as $b) {
            if (! isset($b['start'], $b['end'])) {
                continue;
            }
            [$bsh, $bsm] = explode(':', $b['start']);
            [$beh, $bem] = explode(':', $b['end']);
            $total -= ($beh * 60 + $bem) - ($bsh * 60 + $bsm);
        }

        return max(0, $total);
    }
}
