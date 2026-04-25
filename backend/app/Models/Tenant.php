<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    // IMPORTANT : Les informations sur les clients sont stockées en central
    protected $connection = 'central';

    protected $fillable = [
        'nom', 'database_name', 'domain', 'logo', 'statut'
    ];

    /**
     * Les utilisateurs rattachés à ce tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user', 'tenant_id', 'user_id')
            ->withPivot('role_id', 'is_owner');
    }
}