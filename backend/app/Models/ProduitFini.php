<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProduitFini extends BaseModel
{
    use BelongsToTenant, HasAuditColumns, SoftDeletes;

    protected $table = 'produit_fini';

    protected $fillable = [
        'tenant_id', 'famille_id', 'reference', 'designation', 'detail', 'image_path',
        'taux_tva', 'prix_tva', 'prix_ht', 'prix_ttc', 'created_by'
    ];

    protected $casts = [
        'taux_tva' => 'decimal:3',
        'prix_tva' => 'decimal:2',
        'prix_ht' => 'decimal:2',
        'prix_ttc' => 'decimal:2',
    ];

    // ─── Relations ───
    public function famille()
    {
        return $this->belongsTo(FamilleProduit::class, 'famille_id');
    }

    public function nomenclature()
    {
        return $this->hasMany(NomenclatureProduit::class, 'produit_fini_id');
    }

    public function composants()
    {
        return $this->belongsToMany(Produit::class, 'nomenclature_produit', 'produit_fini_id', 'produit_id')
            ->withPivot('quantite', 'montant_ht', 'montant_tva', 'montant_ttc');
    }
}
