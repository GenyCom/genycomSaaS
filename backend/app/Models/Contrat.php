<?php

namespace App\Models;

use App\Traits\HasAuditColumns;

class Contrat extends BaseModel
{
    use HasAuditColumns;

    protected $table = 'contrats';

    protected $fillable = [
        'titre',
        'numero',
        'client_id',
        'date_debut',
        'date_fin',
        'frequence',
        'prochaine_echeance',
        'statut',
        'total_ht',
        'total_tva',
        'total_ttc',
        'observations',
        'devise_id',
        'taux_change_document',
        'created_by'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'prochaine_echeance' => 'date',
        'taux_change_document' => 'decimal:6'
    ];
    
    public function client() { return $this->belongsTo(Client::class); }
    public function devise() { return $this->belongsTo(Devise::class); }
    public function lignes() { return $this->hasMany(LigneContrat::class, 'contrat_id'); }
}