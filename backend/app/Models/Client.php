<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends BaseModel
{
    use BelongsToTenant, HasAuditColumns, SoftDeletes;

    protected $table = 'clients';
    
    protected static function booted()
    {
        static::saving(function ($client) {
            if ($client->isDirty('solde_initial')) {
                // On met à jour le montant_rest_du avant la sauvegarde
                // On ne peut pas appeler recalculerEncours car il fait un saveQuietly
                // On reproduit la logique ici pour le montant_rest_du
                $facturesSum = $client->factures()
                    ->where(function ($query) {
                        $query->whereDoesntHave('etat', function($q) {
                            $q->where('code', 'BRL');
                        })->orWhereNull('etat_id');
                    })
                    ->sum('montant_restant');
                
                $client->montant_rest_du = ($client->solde_initial ?? 0) + $facturesSum;
            }
        });
    }

    protected $fillable = [
        'tenant_id', 'code_client', 'societe', 'is_personne_physique',
        'civilite', 'nom', 'prenom', 'ice', 'rc',
        'adresse', 'ville', 'code_postal', 'pays',
        'telephone', 'mobile', 'fax', 'email', 'site_web',
        'rib', 'banque', 'image_path', 'observations',
        'exempt_tva', 'type_client_id', 'plafond_credit', 'delai_paiement',
        'montant_rest_du', 'commercial_id', 'created_by', 'if_fiscal', 'patente', 'is_active', 'solde_initial',
    ];

    protected $casts = [
        'is_personne_physique' => 'boolean',
        'exempt_tva' => 'boolean',
        'plafond_credit' => 'decimal:2',
        'montant_rest_du' => 'decimal:2',
        'is_active' => 'boolean',
        'solde_initial' => 'decimal:2',
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
        $this->montant_rest_du = ($this->solde_initial ?? 0) + $this->factures()
            ->where(function ($query) {
                $query->whereDoesntHave('etat', function($q) {
                    $q->where('code', 'BRL'); // Exclure les brouillons
                })->orWhereNull('etat_id'); // Inclure celles sans état défini (sécurité)
            })
            ->sum('montant_restant');
        $this->saveQuietly(); // Use saveQuietly to avoid infinite loops if Client hooks exist
    }
}
