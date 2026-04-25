<?php
namespace App\Models;

use App\Traits\BelongsToTenant;

class LigneBonReception extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'br_lignes';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'br_id',
        'produit_id',
        'designation',
        'quantite_commandee',
        'quantite_recue',
        'prix_unitaire',
        'ordre',
        'created_at',
        'updated_at'
    ];

    public function br()
    {
        return $this->belongsTo(BR::class, 'br_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
