<?php
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

class Facture extends BaseModel
{
    use BelongsToTenant, HasAuditColumns;

    protected $table = 'factures';

    protected $fillable = [
        'tenant_id', 'numero', 'date_facture', 'date_echeance',
        'client_id', 'projet_id', 'devis_id',
        'total_ht', 'total_tva', 'total_ttc', 'total_remise',
        'montant_regle', 'montant_restant',
        'etat_id', 'condition_reglement_id', 'mode_reglement_id',
        'est_reglee', 'date_reglement', 'observations', 'devise_id', 'created_by',
    ];

    protected $casts = [
        'date_facture' => 'date',
        'date_echeance' => 'date',
        'date_reglement' => 'date',
        'est_reglee' => 'boolean',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'montant_regle' => 'decimal:2',
        'montant_restant' => 'decimal:2',
    ];

    // ─── Relations ───
    public function client()            { return $this->belongsTo(Client::class); }
    public function projet()            { return $this->belongsTo(Projet::class); }
    public function devis()             { return $this->belongsTo(Devis::class); }
    public function etat()              { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function conditionReglement(){ return $this->belongsTo(ConditionReglement::class); }
    public function modeReglement()     { return $this->belongsTo(ModeReglement::class); }
    public function lignes()            { return $this->hasMany(LigneFacture::class); }
    public function reglements()        { return $this->morphMany(Reglement::class, 'payable'); }
    public function echeances()         { return $this->morphMany(Echeance::class, 'echeancable'); }
    public function avoirs()            { return $this->hasMany(AvoirClient::class); }
    public function createur()          { return $this->belongsTo(User::class, 'created_by'); }

    // ─── Méthodes ───
    public function recalculerTotaux(): self
    {
        $this->total_ht  = $this->lignes()->sum('montant_ht');
        $this->total_tva = $this->lignes()->sum('montant_tva');
        $this->total_ttc = $this->lignes()->sum('montant_ttc');
        $this->total_remise = $this->lignes()->sum('remise_montant');
        $this->montant_restant = $this->total_ttc - $this->montant_regle;
        $this->save();
        return $this;
    }

    public function enregistrerReglement(float $montant): void
    {
        $this->montant_regle += $montant;
        $this->montant_restant = max(0, $this->total_ttc - $this->montant_regle);
        
        if ($this->montant_restant <= 0) {
            $this->est_reglee = true;
            $this->date_reglement = now()->toDateString();
            // Passer à l'état "Payée"
            $etatPaye = EtatDocument::where('tenant_id', $this->tenant_id)
                ->where('type_document', 'facture')
                ->where('code', 'PAY')->first();
            if ($etatPaye) $this->etat_id = $etatPaye->id;
        } else {
            // Partielle
            $etatPartielle = EtatDocument::where('tenant_id', $this->tenant_id)
                ->where('type_document', 'facture')
                ->where('code', 'PPY')->first();
            if ($etatPartielle) $this->etat_id = $etatPartielle->id;
        }
        
        $this->save();
    }

    // ─── Scopes ───
    public function scopeImpayees($query)
    {
        return $query->where('est_reglee', false)->whereNotNull('numero');
    }

    public function scopeEnRetard($query)
    {
        return $query->where('est_reglee', false)
                     ->whereNotNull('date_echeance')
                     ->where('date_echeance', '<', now());
    }
}
