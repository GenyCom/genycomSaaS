<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends BaseModel
{
    use BelongsToTenant, HasAuditColumns, SoftDeletes;

    protected $table = 'produits';

    protected $fillable = [
        'tenant_id', 'famille_id', 'fournisseur_id', 'reference', 'reference_fournisseur',
        'code_barre', 'marque', 'designation', 'detail', 'unite', 'image_path',
        'is_service', 'is_actif',
        'prix_ht_achat', 'taux_tva', 'prix_ttc_achat', 'prix_revient', 'desc_revient',
        'prix_ht_vente', 'marge_pourcentage', 'prix_ttc_vente',
        'stock_actuel', 'stock_initial', 'seuil_alerte', 'stock_min', 'stock_max',
        'emplacement_stock', 'delai_appro', 'poids', 'dimensions',
        'is_perissable', 'is_lot', 'garantie_mois', 'created_by',
    ];

    protected $casts = [
        'is_service' => 'boolean',
        'is_actif' => 'boolean',
        'is_perissable' => 'boolean',
        'is_lot' => 'boolean',
        'prix_ht_achat' => 'decimal:4',
        'prix_ttc_achat' => 'decimal:2',
        'prix_ht_vente' => 'decimal:4',
        'marge_pourcentage' => 'decimal:2',
        'prix_ttc_vente' => 'decimal:2',
        'stock_actuel' => 'decimal:2',
        'garantie_mois' => 'integer',
    ];

    // ─── Relations ───
    public function famille()     { return $this->belongsTo(FamilleProduit::class, 'famille_id'); }
    public function fournisseur() { return $this->belongsTo(Fournisseur::class); }
    public function stocks()      { return $this->hasMany(Stock::class); }
    public function images()      { return $this->hasMany(ProduitImage::class); }
    public function tarifs()      { return $this->hasMany(TarifProduit::class); }
    public function historique()  { return $this->hasMany(HistoriqueProduit::class); }
    public function fichiers()    { return $this->morphMany(Fichier::class, 'fileable'); }

    // ─── Scopes ───
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('designation', 'like', "%{$search}%")
              ->orWhere('reference', 'like', "%{$search}%")
              ->orWhere('code_barre', 'like', "%{$search}%");
        });
    }

    public function scopeActif($query)  { return $query->where('is_actif', true); }
    public function scopeEnAlerte($query)
    {
        return $query->whereColumn('stock_actuel', '<=', 'seuil_alerte')
                     ->where('is_service', false);
    }

    // ─── Calculs ───
    public function getMargeAttribute(): float
    {
        if ($this->prix_ht_achat == 0) return 0;
        return round((($this->prix_ht_vente - $this->prix_ht_achat) / $this->prix_ht_achat) * 100, 2);
    }
}
