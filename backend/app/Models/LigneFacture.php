<?php
namespace App\Models;

use App\Traits\HasLigneCalculs;

class LigneFacture extends BaseModel
{
    use HasLigneCalculs;

    protected $table = 'ligne_facture';
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'facture_id', 'produit_id', 'designation', 'description',
        'quantite', 'unite', 'prix_unitaire', 'taux_tva',
        'remise_pourcent', 'remise_montant',
        'montant_ht', 'montant_tva', 'montant_ttc',
        'ordre', 'source_type', 'is_produit_fini',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:4',
        'taux_tva' => 'decimal:3',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'is_produit_fini' => 'boolean',
    ];

    public function facture() { return $this->belongsTo(Facture::class); }
    public function produit() { return $this->belongsTo(Produit::class); }
}
