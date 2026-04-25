<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class Echeance extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'echeancier';

    protected $fillable = [
        'tenant_id',
        'echeancable_type',
        'echeancable_id',
        'numero_echeance',
        'date_echeance',
        'montant',
        'montant_regle',
        'statut'
    ];

    protected $casts = [
        'date_echeance' => 'date'
    ];

    public function echeancable() { return $this->morphTo(); }
}
