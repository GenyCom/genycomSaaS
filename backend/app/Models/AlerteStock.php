<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class AlerteStock extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'alertes_stock';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'produit_id',
        'type_alerte',
        'message',
        'est_lue',
        'est_traitee'
    ];

    protected $casts = [
        'est_lue' => 'boolean',
        'est_traitee' => 'boolean'
    ];

    public function produit() { return $this->belongsTo(Produit::class); }
}
