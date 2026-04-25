<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class TarifProduit extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'tarifs_produit';

    protected $fillable = [
        'tenant_id',
        'produit_id',
        'type_client_id',
        'quantite_min',
        'prix_ht',
        'date_debut',
        'date_fin'
    ];

    public function produit()    { return $this->belongsTo(Produit::class); }
    public function typeClient() { return $this->belongsTo(TypeClient::class); }
}
