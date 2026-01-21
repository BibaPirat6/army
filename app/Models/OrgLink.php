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
}
