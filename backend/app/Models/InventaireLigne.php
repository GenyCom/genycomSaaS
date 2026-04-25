<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class InventaireLigne extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'inventaire_lignes';

    protected $fillable = [
        'tenant_id',
        'inventaire_id',
        'produit_id',
        'stock_theorique',
        'stock_physique',
        'ecart',
        'observations'
    ];

    public function inventaire() { return $this->belongsTo(Inventaire::class); }
    public function produit()    { return $this->belongsTo(Produit::class); }
}
