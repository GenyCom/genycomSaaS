<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class Depense extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'depenses';

    // Correspondance stricte avec ta base de données MariaDB
    protected $fillable = [
        'tenant_id',
        'devise_id',
        'taux_change_document',
        'code',
        'libelle',
        'date_depense',
        'montant',
        'categorie_id',
        'etat_id',
        'note_reglement',
        'is_recurrente',
        'frequence',
        'derniere_notification_at',
        'created_by'
    ];

    protected $casts = [
        'date_depense' => 'date',
        'montant' => 'decimal:2',
        'taux_change_document' => 'decimal:6',
        'is_recurrente' => 'boolean',
        'derniere_notification_at' => 'datetime'
    ];

    public function categorie()  
    { 
        return $this->belongsTo(CategorieDepense::class, 'categorie_id'); 
    }

    public function devise()    
    { 
        return $this->belongsTo(Devise::class); 
    }
    
    // Note : La relation fournisseur() a été retirée car tu n'as pas de colonne fournisseur_id
}