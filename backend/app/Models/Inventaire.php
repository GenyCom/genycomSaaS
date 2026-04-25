<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class Inventaire extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'inventaires';

    protected $fillable = [
        'tenant_id',
        'code',
        'date_inventaire',
        'entrepot_id',
        'statut',
        'etat_id',
        'observations',
        'valide_par',
        'date_validation',
        'created_by'
    ];

    protected $casts = [
        'date_inventaire' => 'date',
        'date_validation' => 'datetime'
    ];

    public function entrepot() { return $this->belongsTo(Entrepot::class); }
    public function etat()     { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function lignes()   { return $this->hasMany(InventaireLigne::class); }
}
