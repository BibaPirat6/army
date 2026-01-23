<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgLink extends Model
{
    protected $table = 'org_links';

    protected $fillable = [
        'parent_type',
        'parent_id',
        'child_type',
        'child_id',
        'is_independent',
    ];

    protected $casts = [
        'is_independent' => 'boolean',
    ];

    /**
     * Получить полиморфного родителя
     */
    public function getParent()
    {
        $modelClass = match($this->parent_type) {
            'commissariat' => Commissariat::class,
            'department' => Department::class,
            default => null,
        };

        if ($modelClass) {
            return $modelClass::find($this->parent_id);
        }
        return null;
    }

    /**
     * Получить полиморфного ребенка
     */
    public function getChild()
    {
        $modelClass = match($this->child_type) {
            'department' => Department::class,
            'division' => Division::class,
            'employee' => Employee::class,
            'position' => Position::class,
            default => null,
        };

        if ($modelClass) {
            return $modelClass::find($this->child_id);
        }
        return null;
    }
}
