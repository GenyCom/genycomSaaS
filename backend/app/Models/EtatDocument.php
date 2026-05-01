<?php

namespace App\Models;

class EtatDocument extends BaseModel
{
    protected $table = 'etat_document';

    protected $fillable = [
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
