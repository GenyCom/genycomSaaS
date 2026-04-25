<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class TypeClient extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'type_client';

    protected $fillable = [
        'tenant_id',
        'libelle',
        'detail',
        'exempt_tva',
        'vip'
    ];

    protected $casts = [
        'exempt_tva' => 'boolean',
        'vip' => 'boolean'
    ];
}
