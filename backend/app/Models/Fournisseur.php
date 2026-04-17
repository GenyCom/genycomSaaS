<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class Fournisseur extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'fournisseurs';

    protected $fillable = [
        'tenant_id', 'code_fournisseur', 'societe', 'is_personne_physique',
        'civilite', 'nom', 'prenom', 'ice', 'rc',
        'adresse', 'ville', 'code_postal', 'pays',
        'telephone', 'mobile', 'fax', 'email', 'site_web',
        'rib', 'banque', 'image_path', 'observations',
        'type_fournisseur_id', 'delai_livraison', 'created_by',
    ];

    protected $casts = ['is_personne_physique' => 'boolean'];

    public function typeFournisseur() { return $this->belongsTo(TypeFournisseur::class); }
    public function contacts()        { return $this->morphMany(Contact::class, 'contactable'); }
    public function commandes()       { return $this->hasMany(Commande::class); }
    public function dettes()          { return $this->hasMany(DetteFournisseur::class); }
    public function produits()        { return $this->hasMany(Produit::class); }
    public function fichiers()        { return $this->morphMany(Fichier::class, 'fileable'); }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('societe', 'like', "%{$search}%")
              ->orWhere('nom', 'like', "%{$search}%")
              ->orWhere('code_fournisseur', 'like', "%{$search}%");
        });
    }
}
