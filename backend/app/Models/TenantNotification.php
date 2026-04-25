<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class TenantNotification extends DatabaseNotification
{
    /**
     * Force l'utilisation de la connexion 'tenant' pour ce modèle.
     */
    protected $connection = 'tenant';
    
    /**
     * La table est déjà définie dans le parent comme 'notifications'.
     */
}
