<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    // Fixe la connexion par défaut pour tous les modèles métier vers "tenant"
    protected $connection = 'tenant';

    /**
     * Relation avec l'utilisateur créateur (Base centrale).
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
