<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class AvoirClient extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'avoirs_client';

    protected $fillable = [
        'tenant_id',
        'numero',
        'date_avoir',
        'client_id',
        'facture_id',
        'total_ht',
        'total_tva',
        'total_ttc',
        'etat_id',
        'montant_regle',
        'est_reglee',
        'motif',
        'observations',
        'devise_id',
        'taux_change_document',
        'created_by'
    ];

    protected $casts = [
        'date_avoir' => 'date:Y-m-d',
        'est_reglee' => 'boolean',
        'taux_change_document' => 'decimal:6'
    ];

    public function client()  { return $this->belongsTo(Client::class); }
    public function facture() { return $this->belongsTo(Facture::class); }
    public function devise()  { return $this->belongsTo(Devise::class); }
    public function etat()    { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function lignes()  { return $this->hasMany(LigneAvoirClient::class, 'avoir_id'); }
}
