<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class Stock extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'stocks';

    protected $fillable = [
        'tenant_id',
        'produit_id',
        'entrepot_id',
        'quantite',
        'lot_numero',
        'date_peremption',
        'notes'
    ];

    protected $casts = [
        'quantite' => 'decimal:2'
    ];

    protected $appends = [
        'quantite_physique',
        'quantite_reservee',
        'quantite_disponible'
    ];

    public function getQuantitePhysiqueAttribute()
    {
        return $this->quantite;
    }

    public function getQuantiteReserveeAttribute()
    {
        // Sera implémenté plus tard avec les commandes clients non livrées
        return 0;
    }

    public function getQuantiteDisponibleAttribute()
    {
        return $this->quantite_physique - $this->quantite_reservee;
    }

    public function produit()    { return $this->belongsTo(Produit::class); }
    public function entrepot()   { return $this->belongsTo(Entrepot::class); }
    public function mouvements() { return $this->hasMany(MouvementStock::class, 'stock_id'); }
}
