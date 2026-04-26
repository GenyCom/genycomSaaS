<?php

namespace App\Models;

use App\Traits\HasLigneCalculs;

class LigneContrat extends BaseModel
{
    use HasLigneCalculs;

    protected $table = 'ligne_contrat';

    protected $fillable = [
        'contrat_id',
        'produit_id',
        'designation',
        'description',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'ordre'
    ];

    public function contrat() { return $this->belongsTo(Contrat::class, 'contrat_id'); }
    public function produit() { return $this->belongsTo(Produit::class); }
}