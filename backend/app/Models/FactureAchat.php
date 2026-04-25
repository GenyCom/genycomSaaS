<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class FactureAchat extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'factures_achats';

    protected $fillable = [
        'tenant_id',
        'numero',
        'fournisseur_id',
        'date_facture',
        'date_echeance',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'montant_paye',
        'reste_a_payer',
        'statut',
        'devise_id',
        'taux_change_document',
        'observations',
        'created_by'
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_echeance' => 'date',
        'taux_change_document' => 'decimal:6',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class);
    }

    public function lignes()
    {
        return $this->hasMany(FactureAchatLigne::class, 'facture_achat_id');
    }

    public function receptionNotes()
    {
        return $this->belongsToMany(BR::class, 'br_facture', 'facture_achat_id', 'br_id');
    }

    public function paiements()
    {
        return $this->hasMany(PaiementFournisseur::class, 'facture_achat_id');
    }

    public function recalculerTotaux(): self
    {
        $this->montant_ht  = $this->lignes()->sum('montant_ht');
        $this->montant_tva = $this->lignes()->sum('montant_tva');
        $this->montant_ttc = $this->lignes()->sum('montant_ttc');
        $this->reste_a_payer = $this->montant_ttc - ($this->montant_paye ?? 0);
        $this->save();
        return $this;
    }
}
