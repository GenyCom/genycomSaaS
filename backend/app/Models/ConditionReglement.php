<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class ConditionReglement extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'condition_reglement';

    protected $fillable = [
        'tenant_id',
        'libelle',
        'detail',
        'nombre_jours'
    ];
}
