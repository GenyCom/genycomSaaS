<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class BonCommandeClient extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'bons_commande_client';

    protected $fillable = [
        'tenant_id', 'numero', 'date_commande', 'date_livraison_prevue',
        'client_id', 'projet_id', 'devis_id',
        'total_ht', 'total_tva', 'total_ttc', 'total_remise',
        'etat_id', 'observations', 'conditions',
        'est_livre', 'est_facture', 'devise_id', 'taux_change_document', 'created_by',
    ];

    protected $casts = [
        'date_commande' => 'date:Y-m-d',
        'date_livraison_prevue' => 'date:Y-m-d',
        'est_livre' => 'boolean',
        'est_facture' => 'boolean',
        'taux_change_document' => 'decimal:6',
    ];

    public function client()  { return $this->belongsTo(Client::class); }
    public function projet()  { return $this->belongsTo(Projet::class); }
    public function devise()  { return $this->belongsTo(Devise::class); }
    public function devis()   { return $this->belongsTo(Devis::class); }
    public function etat()    { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function lignes()  { return $this->hasMany(LigneBonCommandeClient::class)->orderBy('ordre'); }
    public function bonsLivraison() { return $this->hasMany(BonLivraison::class); }
    public function factures() { return $this->hasMany(Facture::class); }

    public function recalculerTotaux(): self
    {
        $this->total_ht  = $this->lignes()->sum('montant_ht');
        $this->total_tva = $this->lignes()->sum('montant_tva');
        $this->total_ttc = $this->lignes()->sum('montant_ttc');
        $this->save();
        return $this;
    }
}
