<?php

namespace App\Models;

class ProduitImage extends BaseModel
{
    protected $table = 'produit_images';
    public $timestamps = false;

    protected $fillable = [
        'produit_id',
        'image_path',
        'is_principale',
        'ordre'
    ];

    public function produit() { return $this->belongsTo(Produit::class); }
}
