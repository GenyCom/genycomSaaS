<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\TenantNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // IMPORTANT : Toujours sur la base centrale
    protected $connection = 'central';

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'is_superadmin', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_superadmin' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Les entreprises (tenants) auxquelles cet utilisateur a accès.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user', 'user_id', 'tenant_id')
            ->withPivot('role_id', 'is_owner');
    }

    /**
     * Accessor pour récupérer le nom complet.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Accessor pour le tenant actuel (par exemple le premier).
     */
    public function getTenantAttribute()
    {
        return $this->tenants->first();
    }

    /**
     * Vérifie si l'utilisateur est propriétaire du tenant actuel.
     */
    public function getIsOwnerAttribute(): bool
    {
        return $this->tenant ? (bool) $this->tenant->pivot->is_owner : false;
    }

    /**
     * Surcharge pour utiliser la table notifications du tenant.
     */
    public function notifications()
    {
        return $this->morphMany(TenantNotification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
}