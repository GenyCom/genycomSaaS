<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    // Fixe la connexion par défaut pour tous les modèles métier vers "tenant"
    protected $connection = 'tenant';

    protected $guarded = ['id'];
}
