<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class FamilleProduit extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'famille_produit';

    protected $fillable = [
        'tenant_id',
        'code',
        'libelle',
        'detail',
        'parent_id'
    ];

    public function parent()   { return $this->belongsTo(self::class, 'parent_id'); }
    public function enfants()  { return $this->hasMany(self::class, 'parent_id'); }
    public function produits() { return $this->hasMany(Produit::class, 'famille_id'); }
}
