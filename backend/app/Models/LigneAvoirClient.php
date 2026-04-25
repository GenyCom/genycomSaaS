<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasLigneCalculs;

class LigneAvoirClient extends BaseModel
{
    use BelongsToTenant, HasLigneCalculs;

    protected $table = 'ligne_avoir_client';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'avoir_id',
        'produit_id',
        'designation',
        'quantite',
        'unite',
        'prix_unitaire',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
        'ordre'
    ];

    public function avoir()   { return $this->belongsTo(AvoirClient::class, 'avoir_id'); }
    public function produit() { return $this->belongsTo(Produit::class); }
}
