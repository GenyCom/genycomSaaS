<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class TauxTva extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'taux_tva';

    protected $fillable = [
        'tenant_id',
        'taux',
        'libelle',
        'detail',
        'actif',
        'is_default'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'is_default' => 'boolean',
        'taux' => 'decimal:3'
    ];
}
