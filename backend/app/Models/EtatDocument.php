<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class EtatDocument extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'etat_document';

    protected $fillable = [
        'tenant_id',
        'type_document',
        'code',
        'libelle',
        'ordre',
        'couleur',
        'detail',
        'is_system'
    ];

    protected $casts = [
        'is_system' => 'boolean'
    ];
    
    public function scopeOfType($q, string $type) { return $q->where('type_document', $type); }
    public function scopeByCode($q, string $code)  { return $q->where('code', $code); }
}
