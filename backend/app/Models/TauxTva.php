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
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'taux' => 'decimal:3'
    ];
}
