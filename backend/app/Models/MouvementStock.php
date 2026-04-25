<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class MouvementStock extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'mouvements_stock';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'stock_id',
        'produit_id',
        'type_mouvement',
        'quantite',
        'libelle',
        'reference_document',
        'document_type',
        'document_id',
        'entrepot_source_id',
        'entrepot_dest_id',
        'prix_unitaire',
        'created_by'
    ];

    public function stock()   { return $this->belongsTo(Stock::class); }
    public function produit() { return $this->belongsTo(Produit::class); }
    public function auteur()  { return $this->belongsTo(User::class, 'created_by'); }
}
