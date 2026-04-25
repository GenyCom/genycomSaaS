<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class Fichier extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'fichiers';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'fileable_type',
        'fileable_id',
        'nom_original',
        'nom_stocke',
        'chemin',
        'mime_type',
        'taille',
        'categorie',
        'uploaded_by'
    ];

    public function fileable() { return $this->morphTo(); }
}
