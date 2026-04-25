<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasLigneCalculs;

class LigneBonCommandeClient extends BaseModel
{
    use BelongsToTenant, HasLigneCalculs;

    protected $table = 'ligne_bon_commande_client';

    protected $fillable = [
        'tenant_id', 'bon_commande_client_id', 'produit_id', 'designation', 'description',
        'quantite', 'unite', 'prix_unitaire', 'taux_tva',
        'remise_pourcent', 'remise_montant',
        'montant_ht', 'montant_tva', 'montant_ttc', 'ordre'
    ];

    public function bonCommande() { return $this->belongsTo(BonCommandeClient::class, 'bon_commande_client_id'); }
    public function produit()     { return $this->belongsTo(Produit::class); }
}
