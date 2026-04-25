<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class TypeFournisseur extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'type_fournisseur';

    protected $fillable = [
        'tenant_id',
        'libelle',
        'detail',
        'vip'
    ];
}
