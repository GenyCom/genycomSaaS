<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonCommandeFournisseur extends BaseModel
{
    use BelongsToTenant, HasAuditColumns, SoftDeletes;

    protected $table = 'bcf';

    protected $fillable = [
        'tenant_id',
        'numero',
        'date_commande',
        'date_livraison_prevue',
        'fournisseur_id',
        'total_ht',
        'total_tva',
        'total_ttc',
        'statut',
        'etat_id',
        'observations',
        'devise_id',
        'taux_change_document',
        'entrepot_id',
        'created_by'
    ];

    protected $casts = [
        'date_commande' => 'date:Y-m-d',
        'date_livraison_prevue' => 'date:Y-m-d',
        'taux_change_document' => 'decimal:6',
    ];

    public function fournisseur()    { return $this->belongsTo(Fournisseur::class); }
    public function devise()         { return $this->belongsTo(Devise::class); }
    public function etat()           { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function lignes()         { return $this->hasMany(LigneBonCommandeFournisseur::class, 'bcf_id')->orderBy('ordre'); }
    public function bonsReception()  { return $this->hasMany(BR::class, 'commande_id'); }
    public function dettes()         { return $this->hasMany(DetteFournisseur::class, 'commande_id'); }

    public function recalculerTotaux(): self
    {
        $this->total_ht  = $this->lignes()->sum('montant_ht');
        $this->total_tva = $this->lignes()->sum('montant_tva');
        $this->total_ttc = $this->lignes()->sum('montant_ttc');
        $this->save();
        return $this;
    }

    public function entrepot()
    {
        return $this->belongsTo(Entrepot::class);
    }
}
