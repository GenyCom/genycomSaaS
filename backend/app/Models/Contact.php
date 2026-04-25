<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class Contact extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'contacts';

    protected $fillable = [
        'tenant_id',
        'contactable_type',
        'contactable_id',
        'nom',
        'prenom',
        'fonction',
        'email',
        'telephone',
        'mobile',
        'is_principal'
    ];

    protected $casts = [
        'is_principal' => 'boolean'
    ];

    public function contactable() { return $this->morphTo(); }
}
