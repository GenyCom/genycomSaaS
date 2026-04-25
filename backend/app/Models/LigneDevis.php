<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasLigneCalculs;

class LigneDevis extends BaseModel
{
    use BelongsToTenant, HasLigneCalculs;

    protected $table = 'ligne_devis';

    protected $fillable = [
        'tenant_id', 'devis_id', 'produit_id', 'designation', 'description',
        'quantite', 'unite', 'prix_unitaire', 'taux_tva',
        'remise_pourcent', 'remise_montant',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'ordre', 'is_produit_fini',
    ];

    protected $casts = [
        'quantite' => 'decimal:2', 'prix_unitaire' => 'decimal:4',
        'taux_tva' => 'decimal:3', 'montant_ht' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function devis()   { return $this->belongsTo(Devis::class); }
    public function produit() { return $this->belongsTo(Produit::class); }
}
