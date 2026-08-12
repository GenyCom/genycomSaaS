<?php
namespace App\Models;

use App\Traits\BelongsToTenant;

class NomenclatureProduit extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'nomenclature_produit';
    public $timestamps = true;

    protected $fillable = [
        'tenant_id', 'produit_fini_id', 'produit_id', 'quantite', 'montant_ht', 'montant_tva', 'montant_ttc'
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    // ─── Relations ───
    public function produitFini()
    {
        return $this->belongsTo(ProduitFini::class, 'produit_fini_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
}
