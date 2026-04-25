<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class Devis extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'devis';

    protected $fillable = [
        'tenant_id', 'numero', 'date_devis', 'date_validite',
        'client_id', 'projet_id', 'numero_bon_commande',
        'total_ht', 'total_tva', 'total_ttc', 'total_remise',
        'etat_id', 'observations', 'conditions',
        'est_facture', 'est_livre', 'devise_id', 'taux_change_document', 'created_by',
    ];
 
    protected $casts = [
        'date_devis' => 'date:Y-m-d',
        'date_validite' => 'date:Y-m-d',
        'est_facture' => 'boolean',
        'est_livre' => 'boolean',
        'taux_change_document' => 'decimal:6',
    ];
 
    public function client()  { return $this->belongsTo(Client::class); }
    public function projet()  { return $this->belongsTo(Projet::class); }
    public function devise()  { return $this->belongsTo(Devise::class); }
    public function etat()    { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function lignes()  { return $this->hasMany(LigneDevis::class)->orderBy('ordre'); }
    public function factures(){ return $this->hasMany(Facture::class); }
    public function bonsCommande() { return $this->hasMany(BonCommandeClient::class); }
    public function bonsLivraison() { return $this->hasMany(BonLivraison::class); }

    public function recalculerTotaux(): self
    {
        $this->total_ht  = $this->lignes()->sum('montant_ht');
        $this->total_tva = $this->lignes()->sum('montant_tva');
        $this->total_ttc = $this->lignes()->sum('montant_ttc');
        $this->save();
        return $this;
    }
}
