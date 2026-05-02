<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class BR extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'br';

    protected $fillable = [
        'tenant_id',
        'bcf_id', 
        'numero',
        'date_reception',
        'fournisseur_id',
        'entrepot_id',
        'statut',
        'observations',
        'created_by'
    ];

    protected $casts = [
        'date_reception' => 'date',
    ];

    public function commande()
    {
        return $this->belongsTo(BonCommandeFournisseur::class, 'bcf_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneBonReception::class, 'br_id');
    }

    public function factures()
    {
        return $this->belongsToMany(FactureAchat::class, 'br_facture', 'br_id', 'facture_achat_id');
    }

    public function entrepot()
    {
        return $this->belongsTo(Entrepot::class);
    }
}
