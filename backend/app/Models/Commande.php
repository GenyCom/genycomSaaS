<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class Commande extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'commandes';

    protected $fillable = [
        'tenant_id', 'numero', 'date_commande', 'date_livraison_prevue',
        'fournisseur_id', 'total_ht', 'total_tva', 'total_ttc',
        'etat_id', 'est_livree', 'observations', 'devise_id', 'created_by',
    ];

    protected $casts = [
        'date_commande' => 'date',
        'date_livraison_prevue' => 'date',
        'est_livree' => 'boolean',
    ];

    public function fournisseur()    { return $this->belongsTo(Fournisseur::class); }
    public function etat()           { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function lignes()         { return $this->hasMany(LigneCommande::class)->orderBy('ordre'); }
    public function bonsReception()  { return $this->hasMany(BonReception::class); }
    public function dettes()         { return $this->hasMany(DetteFournisseur::class); }

    public function recalculerTotaux(): self
    {
        $this->total_ht  = $this->lignes()->sum('montant_ht');
        $this->total_tva = $this->lignes()->sum('montant_tva');
        $this->total_ttc = $this->lignes()->sum('montant_ttc');
        $this->save();
        return $this;
    }
}
