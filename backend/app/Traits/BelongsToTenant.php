<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Ce trait permet de filtrer automatiquement les modèles par tenant_id.
 * Il injecte également le tenant_id lors de la création d'un enregistrement.
 */
trait BelongsToTenant
{
    /**
     * Boot du trait : s'exécute automatiquement lors de l'instanciation du modèle.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $tenant = request()->get('current_tenant');
                if ($tenant) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });

        // 2. Global Scope pour filtrer toutes les requêtes (SELECT) par le tenant actuel
        static::addGlobalScope('tenant', function (Builder $builder) {
            $model = $builder->getModel();
            
            // Si on tape direct dans la base 'tenant', la base est déjà isolée.
            if ($model->getConnectionName() === 'tenant') {
                return;
            }

            $tenant = request()->get('current_tenant');
            if ($tenant) {
                $builder->where($model->getTable() . '.tenant_id', $tenant->id);
            }
        });
    }

    /**
     * Relation avec le Tenant (Base centrale).
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}