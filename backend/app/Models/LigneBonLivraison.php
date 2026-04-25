<?php
namespace App\Models;

use \App\Traits\BelongsToTenant;
use \App\Traits\HasLigneCalculs;

class LigneBonLivraison extends BaseModel
{
    use BelongsToTenant, HasLigneCalculs;
    protected $table = 'ligne_bon_livraison';

    protected $fillable = [
        'tenant_id',
        'bon_livraison_id',
        'produit_id',
        'designation',
        'quantite_prevue',
        'quantite_livree',
        'ordre',
        'prix_unitaire',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
    ];

    public function bonLivraison() { return $this->belongsTo(BonLivraison::class, 'bon_livraison_id'); }
    public function produit()      { return $this->belongsTo(Produit::class); }
}
