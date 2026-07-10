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

    protected $casts = [
        'quantite_prevue' => 'decimal:2',
        'quantite_livree' => 'decimal:2',
        'prix_unitaire' => 'decimal:4',
        'taux_tva' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function bonLivraison() { return $this->belongsTo(BonLivraison::class, 'bon_livraison_id'); }
    public function produit()      { return $this->belongsTo(Produit::class); }
}
