<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes;
    
    // Set to central database
    protected $connection = 'central';

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'is_superadmin', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_superadmin' => 'boolean',
    ];

    protected $appends = ['full_name'];

    // ─── Relations ───
    public function tenants() { 
        return $this->belongsToMany(Tenant::class)
                    ->withPivot('role_id', 'is_owner')
                    ->withTimestamps(); 
    }

    // ─── Accessors ───
    public function getFullNameAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }
}
