<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasLigneCalculs;

class LigneBonCommandeFournisseur extends BaseModel
{
    use BelongsToTenant, HasLigneCalculs;

    protected $table = 'bcf_lignes';
    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'bcf_id', 'produit_id', 'designation',
        'quantite', 'prix_unitaire', 'taux_tva', 'remise_montant',
        'montant_ht', 'montant_tva', 'montant_ttc', 'ordre',
    ];

    public function commande() { return $this->belongsTo(BonCommandeFournisseur::class, 'bcf_id'); }
    public function produit()  { return $this->belongsTo(Produit::class); }
}
