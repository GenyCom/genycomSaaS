<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Tenant;
use App\Models\Devis;
use App\Models\BonCommandeClient;
use App\Models\BonLivraison;
use App\Models\Facture;
use App\Models\BCF;
use App\Models\BCFLigne;
use App\Models\BR;
use App\Models\FactureAchat;
use Illuminate\Support\Facades\DB;

class BusinessWorkflowTest extends TestCase
{
    protected $user;
    protected $tenant;
    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Récupérer les données de base pour le test
        $this->user = User::where('email', 'admin@admin.com')->first() ?: User::first();
        $this->tenant = Tenant::first();

        if (!$this->user || !$this->tenant) {
            $this->markTestSkipped('Utilisateur ou Tenant manquant pour le test.');
        }

        $this->headers = [
            'X-Tenant-ID' => $this->tenant->id,
            'Accept' => 'application/json'
        ];
    }

    /**
     * TEST CYCLE DE VENTE : Devis -> BCC -> BL -> Facture
     */
    public function test_sales_cycle_workflow()
    {
        $client = Client::first() ?: Client::create([
            'tenant_id' => $this->tenant->id,
            'code_client' => 'TEST-CLI',
            'societe' => 'Client Test',
            'nom' => 'Test',
            'email' => 'client@test.com'
        ]);

        $produit = Produit::first() ?: Produit::create([
            'tenant_id' => $this->tenant->id,
            'reference' => 'REF-TEST',
            'designation' => 'Produit de Test',
            'prix_ht_vente' => 100,
            'taux_tva' => 20
        ]);

        // 1. Création Devis
        $devis = Devis::create([
            'tenant_id' => $this->tenant->id,
            'numero' => 'DEV-' . time(),
            'date_devis' => now(),
            'client_id' => $client->id,
            'total_ht' => 100,
            'total_tva' => 20,
            'total_ttc' => 120,
            'created_by' => $this->user->id
        ]);

        $devis->lignes()->create([
            'tenant_id' => $this->tenant->id,
            'designation' => $produit->designation,
            'produit_id' => $produit->id,
            'quantite' => 1,
            'prix_unitaire' => 100,
            'taux_tva' => 20,
            'montant_ht' => 100,
            'montant_ttc' => 120
        ]);

        // 2. Transformer en Bon de Commande (BCC)
        $res = $this->actingAs($this->user)->postJson("/api/workflow/devis-to-bc/{$devis->id}", [], $this->headers);
        $res->assertStatus(200);
        $bccId = $res->json('id');
        $this->assertDatabaseHas('bons_commande_client', ['id' => $bccId, 'devis_id' => $devis->id]);

        // 3. Transformer en Bon de Livraison (BL)
        $res = $this->actingAs($this->user)->postJson("/api/workflow/bc-to-bl/{$bccId}", [], $this->headers);
        $res->assertStatus(200);
        $blId = $res->json('id');
        $this->assertDatabaseHas('bons_livraison', ['id' => $blId, 'bon_commande_client_id' => $bccId]);

        // 4. Transformer en Facture
        $res = $this->actingAs($this->user)->postJson("/api/workflow/bl-to-facture/{$blId}", [], $this->headers);
        $res->assertStatus(200);
        $factureId = $res->json('id');
        $this->assertDatabaseHas('factures', ['id' => $factureId, 'bon_commande_client_id' => $bccId]);

        echo "\n✅ Cycle Vente validé : Devis -> BCC -> BL -> Facture\n";
    }

    /**
     * TEST CYCLE D'ACHAT : BCF -> BR -> Facture d'Achat
     */
    public function test_procurement_cycle_workflow()
    {
        $fournisseur = Fournisseur::first() ?: Fournisseur::create([
            'tenant_id' => $this->tenant->id,
            'code_fournisseur' => 'TEST-FOU',
            'societe' => 'Fournisseur Test',
            'email' => 'fourn@test.com'
        ]);

        $produit = Produit::first();

        // 1. Création BCF
        $bcf = BCF::create([
            'tenant_id' => $this->tenant->id,
            'numero' => 'BCF-' . time(),
            'date_commande' => now(),
            'fournisseur_id' => $fournisseur->id,
            'total_ht' => 500,
            'total_tva' => 100,
            'total_ttc' => 600,
            'statut' => 'brouillon',
            'created_by' => $this->user->id
        ]);

        BCFLigne::create([
            'bcf_id' => $bcf->id,
            'produit_id' => $produit->id,
            'designation' => $produit->designation,
            'quantite' => 5,
            'prix_unitaire' => 100,
            'taux_tva' => 20,
            'montant_ht' => 500,
            'montant_tva' => 100,
            'montant_ttc' => 600
        ]);

        // 2. Transformer en BR (Bon de Réception)
        // URL: /api/workflow/commande-fournisseur-to-br/{id}
        $res = $this->actingAs($this->user)->postJson("/api/workflow/commande-fournisseur-to-br/{$bcf->id}", [], $this->headers);
        $res->assertStatus(200);
        $brId = $res->json('id');
        $this->assertDatabaseHas('br', ['id' => $brId, 'bcf_id' => $bcf->id]);

        // 3. Transformer en Facture d'Achat
        // URL: /api/workflow/br-to-facture
        $res = $this->actingAs($this->user)->postJson("/api/workflow/br-to-facture", ['br_ids' => [$brId]], $this->headers);
        $res->assertStatus(200);
        $factureId = $res->json('id');
        $this->assertDatabaseHas('factures_achats', ['id' => $factureId]);
        
        // Vérification de la liaison Pivot br_facture
        $this->assertDatabaseHas('br_facture', [
            'br_id' => $brId,
            'facture_achat_id' => $factureId
        ]);

        echo "✅ Cycle Achat validé : BCF -> BR -> Facture Achat\n";
    }
}
