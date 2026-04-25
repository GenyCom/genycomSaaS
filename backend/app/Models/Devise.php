<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class Devise extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'devise';

    protected $fillable = [
        'tenant_id',
        'nom',
        'code_iso',
        'symbole',
        'taux_change',
        'is_principale',
        'actif'
    ];

    protected $casts = [
        'is_principale' => 'boolean',
        'actif' => 'boolean'
    ];
}
