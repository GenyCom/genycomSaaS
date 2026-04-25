<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class ModeReglement extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'mode_reglement';

    protected $fillable = [
        'tenant_id',
        'libelle',
        'detail'
    ];
}
