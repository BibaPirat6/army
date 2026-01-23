<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Получить все отделения отдела
     */
    public function divisions()
    {
        $divisionLinks = OrgLink::where('parent_type', 'department')
            ->where('parent_id', $this->id)
            ->where('child_type', 'division')
            ->get();

        return collect($divisionLinks)
            ->map(fn($link) => Division::find($link->child_id))
            ->filter()
            ->values();
    }

    /**
     * Получить всех сотрудников отдела
     */
    public function employees()
    {
        $employeeLinks = OrgLink::where('parent_type', 'department')
            ->where('parent_id', $this->id)
            ->where('child_type', 'employee')
            ->get();

        return collect($employeeLinks)
            ->map(fn($link) => Employee::find($link->child_id))
            ->filter()
            ->values();
    }
}
