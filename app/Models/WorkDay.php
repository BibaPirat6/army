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

        $breaks = is_string($this->breaks)
            ? json_decode($this->breaks, true)
            : ($this->breaks ?? []);

        foreach ($breaks as $b) {
            if (! empty($b['start']) && ! empty($b['end'])) {
                [$bh, $bm] = explode(':', $b['start']);
                [$eh2, $em2] = explode(':', $b['end']);
                $total -= ($eh2 * 60 + $em2) - ($bh * 60 + $bm);
            }
        }

        return max(0, $total);
    }
}
