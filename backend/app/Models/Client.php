<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends BaseModel
{
    use BelongsToTenant, HasAuditColumns, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'tenant_id', 'code_client', 'societe', 'is_personne_physique',
        'civilite', 'nom', 'prenom', 'ice', 'rc',
        'adresse', 'ville', 'code_postal', 'pays',
        'telephone', 'mobile', 'fax', 'email', 'site_web',
        'rib', 'banque', 'image_path', 'observations',
        'exempt_tva', 'type_client_id', 'plafond_credit', 'delai_paiement',
        'montant_rest_du', 'commercial_id', 'created_by', 'if_fiscal', 'patente', 'is_active',
    ];

    protected $casts = [
        'is_personne_physique' => 'boolean',
        'exempt_tva' => 'boolean',
        'plafond_credit' => 'decimal:2',
        'montant_rest_du' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['display_name'];

    // ─── Relations ───
    public function typeClient()          { return $this->belongsTo(TypeClient::class); }
    public function commercial()          { return $this->belongsTo(User::class, 'commercial_id'); }
    public function contacts()            { return $this->morphMany(Contact::class, 'contactable'); }
    public function adressesLivraison()   { return $this->hasMany(AdresseLivraison::class); }
    public function adressesFacturation() { return $this->hasMany(AdresseFacturation::class); }
    public function devis()               { return $this->hasMany(Devis::class); }
    public function factures()            { return $this->hasMany(Facture::class); }
    public function projets()             { return $this->hasMany(Projet::class); }
    public function fichiers()            { return $this->morphMany(Fichier::class, 'fileable'); }

    // ─── Accessors ───
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_personne_physique) {
            return trim(($this->civilite ?? '') . ' ' . ($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
        }
        return $this->societe;
    }

    // ─── Scopes ───
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('societe', 'like', "%{$search}%")
              ->orWhere('nom', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('code_client', 'like', "%{$search}%")
              ->orWhere('ice', 'like', "%{$search}%");
        });
    }

    // ─── Utils ───
    public function recalculerEncours(): void
    {
        $this->montant_rest_du = $this->factures()
            ->where(function ($query) {
                $query->whereDoesntHave('etat', function($q) {
                    $q->where('code', 'BRL'); // Exclure les brouillons
                })->orWhereNull('etat_id'); // Inclure celles sans état défini (sécurité)
            })
            ->sum('montant_restant');
        $this->saveQuietly(); // Use saveQuietly to avoid infinite loops if Client hooks exist
    }
}
