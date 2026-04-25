<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class BonLivraison extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'bons_livraison';

    protected $fillable = [
        'tenant_id', 'numero', 'date_livraison',
        'devis_id', 'bon_commande_client_id', 'facture_id', 'projet_id',
        'client_id', 'adresse_livraison_id', 'mode_livraison_id',
        'total_ht', 'total_tva', 'total_ttc', 'total_remise', 'devise_id', 'taux_change_document',
        'observations', 'statut', 'etat_id', 'created_by'
    ];

    protected $casts = [
        'date_livraison' => 'date',
    ];

    public function client()       { return $this->belongsTo(Client::class); }
    public function devis()        { return $this->belongsTo(Devis::class); }
    public function bonCommande()  { return $this->belongsTo(BonCommandeClient::class, 'bon_commande_client_id'); }
    public function facture()      { return $this->belongsTo(Facture::class); }
    public function etat()         { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function lignes()       { return $this->hasMany(LigneBonLivraison::class)->orderBy('ordre'); }

    public function recalculerTotaux(): self
    {
        $this->total_ht  = $this->lignes()->sum('montant_ht');
        $this->total_tva = $this->lignes()->sum('montant_tva');
        $this->total_ttc = $this->lignes()->sum('montant_ttc');
        $this->save();
        return $this;
    }
}
