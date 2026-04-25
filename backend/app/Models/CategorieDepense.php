<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

class CategorieDepense extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'categorie_depense';

    protected $fillable = [
        'tenant_id',
        'libelle',
        'detail',
        'parent_id'
    ];

    public function parent()  { return $this->belongsTo(self::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(self::class, 'parent_id'); }
}
