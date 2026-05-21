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

        $work = ($eh * 60 + $em) - ($sh * 60 + $sm);

        $breaks = $this->breaks;

        if (is_string($breaks)) {
            $breaks = json_decode($breaks, true) ?? [];
        }

        if (! is_array($breaks)) {
            return max(0, $work);
        }

        foreach ($breaks as $b) {
            if (empty($b['start']) || empty($b['end'])) {
                continue;
            }

            [$bh, $bm] = explode(':', $b['start']);
            [$eh2, $em2] = explode(':', $b['end']);

            $work -= (($eh2 * 60 + $em2) - ($bh * 60 + $bm));
        }

        return max(0, $work);
    }
}
