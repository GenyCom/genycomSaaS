<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projet extends BaseModel
{
    use BelongsToTenant, HasAuditColumns, SoftDeletes;

    protected $table = 'projets';

    protected $fillable = [
        'tenant_id',
        'code_projet',
        'nom_projet',
        'description',
        'client_id',
        'type_projet',
        'date_debut',
        'date_fin_prevue',
        'date_fin_reelle',
        'budget_prevu',
        'budget_consomme',
        'devise_id',
        'taux_change_document',
        'statut',
        'etat_id',
        'avancement_pcent',
        'responsable_id',
        'priorite',
        'created_by'
    ];

    protected $casts = [
        'date_debut' => 'date:Y-m-d',         // Ajout de :Y-m-d
        'date_fin_prevue' => 'date:Y-m-d',    // Ajout de :Y-m-d
        'date_fin_reelle' => 'date:Y-m-d',    // Ajout de :Y-m-d
        'taux_change_document' => 'decimal:6'
    ];

    public function client()      { return $this->belongsTo(Client::class); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function etat()         { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function devise()       { return $this->belongsTo(Devise::class); }
    public function devis()       { return $this->hasMany(Devis::class); }
    public function factures()    { return $this->hasMany(Facture::class); }
}
