<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class Entrepot extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'entrepots';

    protected $fillable = [
        'tenant_id',
        'code',
        'nom',
        'adresse',
        'responsable_id',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function stocks()      { return $this->hasMany(Stock::class); }
}
