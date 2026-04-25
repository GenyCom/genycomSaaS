<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class AdresseLivraison extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'adresses_livraison';

    protected $fillable = [
        'tenant_id',
        'client_id',
        'adresse',
        'ville',
        'code_postal',
        'pays',
        'contact',
        'telephone',
        'observations',
        'is_default'
    ];

    public function client() { return $this->belongsTo(Client::class); }
}
