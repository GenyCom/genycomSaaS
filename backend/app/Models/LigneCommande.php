<?php
namespace App\Models;

use App\Traits\HasLigneCalculs;

class LigneCommande extends BaseModel
{
    use HasLigneCalculs;

    protected $table = 'ligne_commande';

    protected $fillable = [
        'commande_id', 'produit_id', 'designation',
        'quantite', 'prix_unitaire', 'taux_tva', 'remise_montant',
        'montant_ht', 'montant_tva', 'montant_ttc', 'ordre',
    ];

    public function commande() { return $this->belongsTo(Commande::class); }
    public function produit()  { return $this->belongsTo(Produit::class); }
}
