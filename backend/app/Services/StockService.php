<?php
namespace App\Services;

use App\Models\{Stock, MouvementStock, AlerteStock, Produit};
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion de stock.
 */
class StockService
{
    /**
     * Entrée en stock.
     */
    public function entree(int $tenantId, int $produitId, float $quantite, string $typeMouvement = 'entree_achat', array $options = []): MouvementStock
    {
        return DB::transaction(function () use ($tenantId, $produitId, $quantite, $typeMouvement, $options) {
            $entrepotId = $options['entrepot_id'] ?? null;
            
            $stock = Stock::firstOrCreate(
                ['tenant_id' => $tenantId, 'produit_id' => $produitId, 'entrepot_id' => $entrepotId],
                ['quantite' => 0]
            );

            $stock->quantite += $quantite;
            $stock->save();

            // Mettre à jour le stock_actuel du produit
            $totalStock = Stock::where('tenant_id', $tenantId)->where('produit_id', $produitId)->sum('quantite');
            Produit::withoutGlobalScopes()->where('id', $produitId)->update(['stock_actuel' => $totalStock]);

            $mouvement = MouvementStock::create([
                'tenant_id'          => $tenantId,
                'stock_id'           => $stock->id,
                'produit_id'         => $produitId,
                'type_mouvement'     => $typeMouvement,
                'quantite'           => $quantite,
                'libelle'            => $options['libelle'] ?? "Entrée: {$typeMouvement}",
                'reference_document' => $options['reference_document'] ?? null,
                'document_type'      => $options['document_type'] ?? null,
                'document_id'        => $options['document_id'] ?? null,
                'entrepot_dest_id'   => $entrepotId,
                'prix_unitaire'      => $options['prix_unitaire'] ?? null,
                'created_by'         => auth()->id(),
            ]);

            $this->verifierAlertes($tenantId, $produitId, $totalStock);

            return $mouvement;
        });
    }

    /**
     * Sortie de stock.
     */
    public function sortie(int $tenantId, int $produitId, float $quantite, string $typeMouvement = 'sortie_vente', array $options = []): MouvementStock
    {
        return DB::transaction(function () use ($tenantId, $produitId, $quantite, $typeMouvement, $options) {
            $entrepotId = $options['entrepot_id'] ?? null;
            
            $stock = Stock::where('tenant_id', $tenantId)
                ->where('produit_id', $produitId)
                ->where('entrepot_id', $entrepotId)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->quantite < $quantite) {
                $disponible = $stock ? $stock->quantite : 0;
                throw new \Exception("Stock insuffisant. Disponible: {$disponible}, Demandé: {$quantite}");
            }

            $stock->quantite -= $quantite;
            $stock->save();

            $totalStock = Stock::where('tenant_id', $tenantId)->where('produit_id', $produitId)->sum('quantite');
            Produit::withoutGlobalScopes()->where('id', $produitId)->update(['stock_actuel' => $totalStock]);

            $mouvement = MouvementStock::create([
                'tenant_id'          => $tenantId,
                'stock_id'           => $stock->id,
                'produit_id'         => $produitId,
                'type_mouvement'     => $typeMouvement,
                'quantite'           => $quantite,
                'libelle'            => $options['libelle'] ?? "Sortie: {$typeMouvement}",
                'reference_document' => $options['reference_document'] ?? null,
                'document_type'      => $options['document_type'] ?? null,
                'document_id'        => $options['document_id'] ?? null,
                'entrepot_source_id' => $entrepotId,
                'created_by'         => auth()->id(),
            ]);

            $this->verifierAlertes($tenantId, $produitId, $totalStock);

            return $mouvement;
        });
    }

    /**
     * Transfert inter-entrepôts.
     */
    public function transfert(int $tenantId, int $produitId, float $quantite, int $entrepotSourceId, int $entrepotDestId): void
    {
        DB::transaction(function () use ($tenantId, $produitId, $quantite, $entrepotSourceId, $entrepotDestId) {
            $this->sortie($tenantId, $produitId, $quantite, 'transfert_out', [
                'entrepot_id' => $entrepotSourceId,
                'libelle' => "Transfert vers entrepôt #{$entrepotDestId}",
            ]);
            $this->entree($tenantId, $produitId, $quantite, 'transfert_in', [
                'entrepot_id' => $entrepotDestId,
                'libelle' => "Transfert depuis entrepôt #{$entrepotSourceId}",
            ]);
        });
    }

    /**
     * Appliquer un inventaire validé (ajustements de stock).
     */
    public function appliquerInventaire(int $tenantId, $inventaire): void
    {
        DB::transaction(function () use ($tenantId, $inventaire) {
            foreach ($inventaire->lignes as $ligne) {
                if ($ligne->ecart == 0) continue;
                
                if ($ligne->ecart > 0) {
                    $this->entree($tenantId, $ligne->produit_id, abs($ligne->ecart), 'inventaire', [
                        'entrepot_id' => $inventaire->entrepot_id,
                        'libelle' => "Ajustement inventaire #{$inventaire->code} (+{$ligne->ecart})",
                    ]);
                } else {
                    $this->sortie($tenantId, $ligne->produit_id, abs($ligne->ecart), 'inventaire', [
                        'entrepot_id' => $inventaire->entrepot_id,
                        'libelle' => "Ajustement inventaire #{$inventaire->code} ({$ligne->ecart})",
                    ]);
                }
            }
        });
    }

    /**
     * Vérifier et créer les alertes de stock.
     */
    private function verifierAlertes(int $tenantId, int $produitId, float $quantiteActuelle): void
    {
        $produit = Produit::withoutGlobalScopes()->find($produitId);
        if (!$produit || $produit->is_service) return;

        // Supprimer les anciennes alertes non traitées pour ce produit
        AlerteStock::where('tenant_id', $tenantId)
            ->where('produit_id', $produitId)
            ->where('est_traitee', false)
            ->delete();

        if ($quantiteActuelle <= 0) {
            AlerteStock::create([
                'tenant_id'  => $tenantId,
                'produit_id' => $produitId,
                'type_alerte'=> 'rupture',
                'message'    => "RUPTURE DE STOCK: {$produit->designation} (Réf: {$produit->reference})",
            ]);
        } elseif ($produit->seuil_alerte > 0 && $quantiteActuelle <= $produit->seuil_alerte) {
            AlerteStock::create([
                'tenant_id'  => $tenantId,
                'produit_id' => $produitId,
                'type_alerte'=> 'seuil_min',
                'message'    => "Stock bas: {$produit->designation} — {$quantiteActuelle} restants (seuil: {$produit->seuil_alerte})",
            ]);
        }
    }
}
