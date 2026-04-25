<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class ModeLivraison extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'mode_livraison';

    protected $fillable = [
        'tenant_id',
        'libelle',
        'detail'
    ];
}
