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
        'nom', 'database_name', 'db_username', 'db_password', 'domain', 'logo', 'statut'
    ];

    /**
     * Les utilisateurs rattachés à ce tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user', 'tenant_id', 'user_id')
            ->withPivot('role_id', 'is_owner');
    }

    /**
     * Configure la connexion 'tenant' pour ce locataire.
     */
    public function configure(): void
    {
        // On purge d'abord pour fermer toute connexion PDO ouverte avec d'anciens credentials
        \Illuminate\Support\Facades\DB::purge('tenant');

        // On définit les paramètres dynamiques
        config([
            'database.connections.tenant.database' => $this->database_name,
            'database.connections.tenant.username' => $this->db_username ?: config('database.connections.central.username'),
            'database.connections.tenant.password' => $this->db_password ?: config('database.connections.central.password'),
        ]);

        // Optionnel : reconnecter immédiatement pour valider
        // \Illuminate\Support\Facades\DB::connection('tenant')->reconnect();
    }
}