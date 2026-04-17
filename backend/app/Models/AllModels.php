<?php
// ═══════════════════════════════════════════════════════════════
// Modèles complémentaires — GenyCom Web SaaS
// ═══════════════════════════════════════════════════════════════
namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;

// ─── Référentiels ───────────────────────────────────────────
class EtatDocument extends BaseModel {
    use BelongsToTenant;
    protected $table = 'etat_document';
    protected $fillable = ['tenant_id','type_document','code','libelle','ordre','couleur','detail','is_system'];
    protected $casts = ['is_system' => 'boolean'];
    
    public function scopeOfType($q, string $type) { return $q->where('type_document', $type); }
    public function scopeByCode($q, string $code)  { return $q->where('code', $code); }
}

class Devise extends BaseModel {
    use BelongsToTenant;
    protected $table = 'devise';
    protected $fillable = ['tenant_id','nom','code_iso','symbole','taux_change','is_principale','actif'];
    protected $casts = ['is_principale' => 'boolean', 'actif' => 'boolean'];
}

class TauxTva extends BaseModel {
    use BelongsToTenant;
    protected $table = 'taux_tva';
    protected $fillable = ['tenant_id','taux','libelle','detail','actif'];
    protected $casts = ['actif' => 'boolean','taux' => 'decimal:3'];
}

class ModeReglement extends BaseModel {
    use BelongsToTenant;
    protected $table = 'mode_reglement';
    protected $fillable = ['tenant_id','libelle','detail'];
}

class ModeLivraison extends BaseModel {
    use BelongsToTenant;
    protected $table = 'mode_livraison';
    protected $fillable = ['tenant_id','libelle','detail'];
}

class ConditionReglement extends BaseModel {
    use BelongsToTenant;
    protected $table = 'condition_reglement';
    protected $fillable = ['tenant_id','libelle','detail','nombre_jours'];
}

class TypeClient extends BaseModel {
    use BelongsToTenant;
    protected $table = 'type_client';
    protected $fillable = ['tenant_id','libelle','detail','exempt_tva','vip'];
    protected $casts = ['exempt_tva' => 'boolean', 'vip' => 'boolean'];
}

class TypeFournisseur extends BaseModel {
    use BelongsToTenant;
    protected $table = 'type_fournisseur';
    protected $fillable = ['tenant_id','libelle','detail','vip'];
}

class FamilleProduit extends BaseModel {
    use BelongsToTenant;
    protected $table = 'famille_produit';
    protected $fillable = ['tenant_id','code','libelle','detail','parent_id'];
    public function parent()   { return $this->belongsTo(self::class, 'parent_id'); }
    public function enfants()  { return $this->hasMany(self::class, 'parent_id'); }
    public function produits() { return $this->hasMany(Produit::class, 'famille_id'); }
}

class SequenceNumerotation extends BaseModel {
    use BelongsToTenant;
    protected $table = 'sequences_numerotation';
    protected $fillable = ['tenant_id','type_document','prefixe','annee','mois','dernier_numero'];
    public $timestamps = true;
}

// ─── Tiers complémentaires ──────────────────────────────────

class Contact extends BaseModel {
    use BelongsToTenant;
    protected $table = 'contacts';
    protected $fillable = ['tenant_id','contactable_type','contactable_id','nom','prenom','fonction','email','telephone','mobile','is_principal'];
    protected $casts = ['is_principal' => 'boolean'];
    public function contactable() { return $this->morphTo(); }
}

class AdresseLivraison extends BaseModel {
    use BelongsToTenant;
    protected $table = 'adresses_livraison';
    protected $fillable = ['tenant_id','client_id','adresse','ville','code_postal','pays','contact','telephone','observations','is_default'];
    public function client() { return $this->belongsTo(Client::class); }
}

class AdresseFacturation extends BaseModel {
    use BelongsToTenant;
    protected $table = 'adresses_facturation';
    protected $fillable = ['tenant_id','client_id','adresse','ville','code_postal','pays','contact','telephone','observations','is_default'];
    public function client() { return $this->belongsTo(Client::class); }
}

// ─── Produits complémentaires ───────────────────────────────

class ProduitImage extends BaseModel {
    protected $table = 'produit_images';
    public $timestamps = false;
    protected $fillable = ['produit_id','image_path','is_principale','ordre'];
    public function produit() { return $this->belongsTo(Produit::class); }
}

class TarifProduit extends BaseModel {
    use BelongsToTenant;
    protected $table = 'tarifs_produit';
    protected $fillable = ['tenant_id','produit_id','type_client_id','quantite_min','prix_ht','date_debut','date_fin'];
    public function produit()    { return $this->belongsTo(Produit::class); }
    public function typeClient() { return $this->belongsTo(TypeClient::class); }
}

class HistoriqueProduit extends BaseModel {
    use BelongsToTenant;
    protected $table = 'historique_produit';
    public $timestamps = false;
    protected $fillable = ['tenant_id','produit_id','action','donnees_avant','donnees_apres','user_id'];
    protected $casts = ['donnees_avant' => 'json', 'donnees_apres' => 'json'];
    public function produit() { return $this->belongsTo(Produit::class); }
    public function user()    { return $this->belongsTo(User::class); }
}

// ─── Ventes complémentaires ─────────────────────────────────

class BonLivraison extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'bons_livraison';
    protected $fillable = ['tenant_id','numero','date_livraison','devis_id','facture_id','client_id','adresse_livraison_id','mode_livraison_id','observations','statut','created_by'];
    public function devis()   { return $this->belongsTo(Devis::class); }
    public function facture() { return $this->belongsTo(Facture::class); }
    public function client()  { return $this->belongsTo(Client::class); }
    public function lignes()  { return $this->hasMany(LigneBonLivraison::class, 'bon_livraison_id'); }
}

class LigneBonLivraison extends BaseModel {
    protected $table = 'ligne_bon_livraison';
    protected $fillable = ['bon_livraison_id','produit_id','designation','quantite_prevue','quantite_livree','ordre'];
    public function bonLivraison() { return $this->belongsTo(BonLivraison::class); }
    public function produit()      { return $this->belongsTo(Produit::class); }
}

class AvoirClient extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'avoirs_client';
    protected $fillable = ['tenant_id','numero','date_avoir','client_id','facture_id','total_ht','total_tva','total_ttc','etat_id','montant_regle','est_reglee','motif','observations','created_by'];
    protected $casts = ['est_reglee' => 'boolean', 'date_avoir' => 'date'];
    public function client()  { return $this->belongsTo(Client::class); }
    public function facture() { return $this->belongsTo(Facture::class); }
    public function lignes()  { return $this->hasMany(LigneAvoirClient::class, 'avoir_id'); }
}

class LigneAvoirClient extends BaseModel {
    use HasLigneCalculs;
    protected $table = 'ligne_avoir_client';
    protected $fillable = ['avoir_id','produit_id','designation','quantite','prix_unitaire','taux_tva','montant_ht','montant_tva','montant_ttc','ordre'];
    public function avoir()   { return $this->belongsTo(AvoirClient::class, 'avoir_id'); }
    public function produit() { return $this->belongsTo(Produit::class); }
}

// ─── Achats complémentaires ─────────────────────────────────

class BonReception extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'bons_reception';
    protected $fillable = ['tenant_id','numero','date_reception','commande_id','fournisseur_id','observations','statut','created_by'];
    public function commande()    { return $this->belongsTo(Commande::class); }
    public function fournisseur() { return $this->belongsTo(Fournisseur::class); }
    public function lignes()      { return $this->hasMany(LigneBonReception::class, 'bon_reception_id'); }
}

class LigneBonReception extends BaseModel {
    protected $table = 'ligne_bon_reception';
    protected $fillable = ['bon_reception_id','produit_id','designation','quantite_commandee','quantite_recue','prix_unitaire','lot_numero','date_peremption','ordre'];
    public function bonReception() { return $this->belongsTo(BonReception::class); }
    public function produit()      { return $this->belongsTo(Produit::class); }
}

class DetteFournisseur extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'dettes_fournisseur';
    protected $fillable = ['tenant_id','numero','fournisseur_id','commande_id','bon_reception_id','date_dette','date_echeance','montant_total','montant_regle','montant_restant','etat_id','mode_reglement_id','observations','created_by'];
    protected $casts = ['date_dette' => 'date', 'date_echeance' => 'date'];
    public function fournisseur()  { return $this->belongsTo(Fournisseur::class); }
    public function commande()     { return $this->belongsTo(Commande::class); }
    public function etat()         { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
    public function reglements()   { return $this->morphMany(Reglement::class, 'payable'); }
}

class AvoirFournisseur extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'avoirs_fournisseur';
    protected $fillable = ['tenant_id','numero','fournisseur_id','dette_id','date_avoir','motif_retour','total_ht','total_tva','total_ttc','montant_regle','etat_id','observations','created_by'];
    public function fournisseur() { return $this->belongsTo(Fournisseur::class); }
    public function dette()       { return $this->belongsTo(DetteFournisseur::class, 'dette_id'); }
    public function lignes()      { return $this->hasMany(LigneAvoirFournisseur::class, 'avoir_id'); }
}

class LigneAvoirFournisseur extends BaseModel {
    use HasLigneCalculs;
    protected $table = 'ligne_avoir_fournisseur';
    protected $fillable = ['avoir_id','produit_id','designation','quantite','prix_unitaire','taux_tva','montant_ht','montant_tva','montant_ttc','ordre'];
    public function avoir()   { return $this->belongsTo(AvoirFournisseur::class, 'avoir_id'); }
    public function produit() { return $this->belongsTo(Produit::class); }
}

// ─── Stock ──────────────────────────────────────────────────

class Entrepot extends BaseModel {
    use BelongsToTenant;
    protected $table = 'entrepots';
    protected $fillable = ['tenant_id','code','nom','adresse','responsable_id','is_default'];
    protected $casts = ['is_default' => 'boolean'];
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function stocks()      { return $this->hasMany(Stock::class); }
}

class Stock extends BaseModel {
    use BelongsToTenant;
    protected $table = 'stocks';
    protected $fillable = ['tenant_id','produit_id','entrepot_id','quantite','lot_numero','date_peremption','notes'];
    protected $casts = ['quantite' => 'decimal:2'];
    public function produit()    { return $this->belongsTo(Produit::class); }
    public function entrepot()   { return $this->belongsTo(Entrepot::class); }
    public function mouvements() { return $this->hasMany(MouvementStock::class, 'stock_id'); }
}

class MouvementStock extends BaseModel {
    use BelongsToTenant;
    protected $table = 'mouvements_stock';
    public $timestamps = false;
    protected $fillable = ['tenant_id','stock_id','produit_id','type_mouvement','quantite','libelle','reference_document','document_type','document_id','entrepot_source_id','entrepot_dest_id','prix_unitaire','created_by'];
    public function stock()   { return $this->belongsTo(Stock::class); }
    public function produit() { return $this->belongsTo(Produit::class); }
    public function auteur()  { return $this->belongsTo(User::class, 'created_by'); }
}

class Inventaire extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'inventaires';
    protected $fillable = ['tenant_id','code','date_inventaire','entrepot_id','statut','observations','valide_par','date_validation','created_by'];
    protected $casts = ['date_inventaire' => 'date', 'date_validation' => 'datetime'];
    public function entrepot() { return $this->belongsTo(Entrepot::class); }
    public function lignes()   { return $this->hasMany(InventaireLigne::class); }
}

class InventaireLigne extends BaseModel {
    protected $table = 'inventaire_lignes';
    protected $fillable = ['inventaire_id','produit_id','stock_theorique','stock_physique','ecart','observations'];
    public function inventaire() { return $this->belongsTo(Inventaire::class); }
    public function produit()    { return $this->belongsTo(Produit::class); }
}

class AlerteStock extends BaseModel {
    use BelongsToTenant;
    protected $table = 'alertes_stock';
    public $timestamps = false;
    protected $fillable = ['tenant_id','produit_id','type_alerte','message','est_lue','est_traitee'];
    protected $casts = ['est_lue' => 'boolean', 'est_traitee' => 'boolean'];
    public function produit() { return $this->belongsTo(Produit::class); }
}

// ─── Finances ───────────────────────────────────────────────

class Reglement extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'reglements';
    protected $fillable = ['tenant_id','payable_type','payable_id','date_reglement','montant','mode_reglement_id','numero_cheque','banque','reference_virement','observations','created_by'];
    protected $casts = ['date_reglement' => 'date', 'montant' => 'decimal:2'];
    public function payable()       { return $this->morphTo(); }
    public function modeReglement() { return $this->belongsTo(ModeReglement::class); }
    public function auteur()        { return $this->belongsTo(User::class, 'created_by'); }
}

class Echeance extends BaseModel {
    use BelongsToTenant;
    protected $table = 'echeancier';
    protected $fillable = ['tenant_id','echeancable_type','echeancable_id','numero_echeance','date_echeance','montant','montant_regle','statut'];
    protected $casts = ['date_echeance' => 'date'];
    public function echeancable() { return $this->morphTo(); }
}

class CategorieDepense extends BaseModel {
    use BelongsToTenant;
    protected $table = 'categorie_depense';
    protected $fillable = ['tenant_id','libelle','detail','parent_id'];
    public function parent()  { return $this->belongsTo(self::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(self::class, 'parent_id'); }
}

class Depense extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'depenses';
    protected $fillable = ['tenant_id','numero','description','note','categorie_id','paye_a','date_depense','montant','etat_id','note_reglement','is_recurrente','frequence','created_by'];
    protected $casts = ['date_depense' => 'date', 'is_recurrente' => 'boolean'];
    public function categorie() { return $this->belongsTo(CategorieDepense::class); }
    public function etat()      { return $this->belongsTo(EtatDocument::class, 'etat_id'); }
}

// ─── Projets ────────────────────────────────────────────────

class Projet extends BaseModel {
    use BelongsToTenant, HasAuditColumns;
    protected $table = 'projets';
    protected $fillable = ['tenant_id','code_projet','nom_projet','description','client_id','date_debut','date_fin_prevue','date_fin_reelle','budget_prevu','budget_consomme','statut','responsable_id','priorite','created_by'];
    protected $casts = ['date_debut' => 'date', 'date_fin_prevue' => 'date', 'date_fin_reelle' => 'date'];
    public function client()      { return $this->belongsTo(Client::class); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function devis()       { return $this->hasMany(Devis::class); }
    public function factures()    { return $this->hasMany(Facture::class); }
}

// ─── Transversal ────────────────────────────────────────────

class Fichier extends BaseModel {
    use BelongsToTenant;
    protected $table = 'fichiers';
    public $timestamps = false;
    protected $fillable = ['tenant_id','fileable_type','fileable_id','nom_original','nom_stocke','chemin','mime_type','taille','categorie','uploaded_by'];
    public function fileable() { return $this->morphTo(); }
}

class ActivityLog extends BaseModel {
    protected $table = 'activity_log';
    public $timestamps = false;
    protected $fillable = ['tenant_id','user_id','action','subject_type','subject_id','description','properties','ip_address','user_agent'];
    protected $casts = ['properties' => 'json'];
    public function user() { return $this->belongsTo(User::class); }
}
