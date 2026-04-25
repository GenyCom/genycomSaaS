<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class HistoriqueProduit extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'historique_produit';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'produit_id',
        'action',
        'donnees_avant',
        'donnees_apres',
        'user_id'
    ];

    protected $casts = [
        'donnees_avant' => 'json',
        'donnees_apres' => 'json'
    ];

    public function produit() { return $this->belongsTo(Produit::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
