<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasLigneCalculs;

class FactureAchatLigne extends BaseModel
{
    use BelongsToTenant, HasLigneCalculs;

    protected $table = 'facture_achat_lignes';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'facture_achat_id',
        'produit_id',
        'designation',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'ordre'
    ];

    public function facture()
    {
        return $this->belongsTo(FactureAchat::class, 'facture_achat_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
