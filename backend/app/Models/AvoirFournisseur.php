<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class AvoirFournisseur extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'avoirs_achats';

    protected $fillable = [
        'tenant_id',
        'numero',
        'fournisseur_id',
        'facture_achat_id',
        'date_avoir',
        'motif',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'statut',
        'devise_id',
        'taux_change_document',
        'created_by'
    ];

    protected $casts = [
        'date_avoir' => 'date:Y-m-d',
        'taux_change_document' => 'decimal:6'
    ];

    public function fournisseur() { return $this->belongsTo(Fournisseur::class); }
    public function facture()     { return $this->belongsTo(FactureAchat::class, 'facture_achat_id'); }
    public function devise()      { return $this->belongsTo(Devise::class); }
    public function lignes()      { return $this->hasMany(LigneAvoirFournisseur::class, 'avoir_achat_id'); }
}
