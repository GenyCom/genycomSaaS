<?php
namespace App\Traits;

/**
 * Trait pour tracer les colonnes d'audit (créateur, modificateur).
 */
trait HasAuditColumns
{
    protected static function bootHasAuditColumns(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?? auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && in_array('updated_by', $model->getFillable())) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
