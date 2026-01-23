<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commissariat extends Model
{
    protected $table = 'commissariats';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Получить все отделы комиссариата
     */
    public function departments()
    {
        $departmentLinks = OrgLink::where('parent_type', 'commissariat')
            ->where('parent_id', $this->id)
            ->where('child_type', 'department')
            ->get();

        return collect($departmentLinks)
            ->map(fn($link) => Department::find($link->child_id))
            ->filter()
            ->values();
    }

    /**
     * Получить все отделения комиссариата
     */
    public function divisions()
    {
        $divisionLinks = OrgLink::where('parent_type', 'commissariat')
            ->where('parent_id', $this->id)
            ->where('child_type', 'division')
            ->get();

        return collect($divisionLinks)
            ->map(fn($link) => Division::find($link->child_id))
            ->filter()
            ->values();
    }

    /**
     * Получить всех сотрудников комиссариата
     */
    public function employees()
    {
        $employeeLinks = OrgLink::where('parent_type', 'commissariat')
            ->where('parent_id', $this->id)
            ->where('child_type', 'employee')
            ->get();

        return collect($employeeLinks)
            ->map(fn($link) => Employee::find($link->child_id))
            ->filter()
            ->values();
    }
}
