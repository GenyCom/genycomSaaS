<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Models\BonCommandeClient;
use App\Models\BonLivraison;
use App\Models\Facture;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesWorkflowTest extends TestCase
{
    // Note: Dans cet environnement, nous ne rafraîchissons pas la DB car elle est partagée.
    // Nous allons simuler les appels API.

    public function test_full_sales_cycle()
    {
        // 1. Setup : On récupère un utilisateur et un client existant ou on les simule
        $user = User::first();
        $tenant = Tenant::first();
        $client = Client::first();

        // 2. Création d'un Devis
        $devis = Devis::create([
            'tenant_id' => $tenant->id,
            'numero' => 'TEST-DEV-001',
            'date_devis' => now(),
            'client_id' => $client->id,
            'total_ht' => 1000,
            'total_ttc' => 1200,
            'created_by' => $user->id,
        ]);

        LigneDevis::create([
            'tenant_id' => $tenant->id,
            'devis_id' => $devis->id,
            'designation' => 'Produit Test',
            'quantite' => 1,
            'prix_unitaire' => 1000,
            'montant_ht' => 1000,
            'montant_ttc' => 1200,
        ]);

        $this->assertDatabaseHas('devis', ['id' => $devis->id]);

        // 3. Transformation Devis -> BCC
        $response = $this->actingAs($user)->postJson("/api/workflow/devis/{$devis->id}/to-bc");
        $response->assertStatus(200);
        $bccId = $response->json('id');
        
        $bcc = BonCommandeClient::find($bccId);
        $this->assertEquals($devis->id, $bcc->devis_id);
        $this->assertEquals(1000, $bcc->total_ht);

        // 4. Transformation BCC -> BL
        $response = $this->actingAs($user)->postJson("/api/workflow/bc/{$bccId}/to-bl");
        $response->assertStatus(200);
        $blId = $response->json('id');

        $bl = BonLivraison::find($blId);
        $this->assertEquals($bccId, $bl->bon_commande_client_id);

        // 5. Transformation BL -> Facture
        $response = $this->actingAs($user)->postJson("/api/workflow/bl/{$blId}/to-facture");
        $response->assertStatus(200);
        $factureId = $response->json('id');

        $facture = Facture::find($factureId);
        $this->assertEquals($bl->id, $blId); // Verification simple
        $this->assertEquals($bccId, $facture->bon_commande_client_id);
        $this->assertEquals(1000, $facture->total_ht);

        echo "Workflow complet validé : Devis -> BCC -> BL -> Facture\n";
    }
}
