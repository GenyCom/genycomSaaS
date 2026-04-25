<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class SequenceNumerotation extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'sequences_numerotation';
    public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'type_document',
        'prefixe',
        'annee',
        'mois',
        'dernier_numero'
    ];
}
