<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\Entrepot;
use App\Models\Facture;
use App\Models\Tenant;
use App\Services\StockService;
use App\Services\IdEncoder;

class PosCheckoutTest extends TestCase
{
    public function test_fast_track_pos_checkout()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found in database');
        }

        $tenant = $user->tenants()->first() ?? Tenant::first();
        if (!$tenant) {
            $this->markTestSkipped('No tenant found in database');
        }

        // Configure tenant connection
        $tenant->configure();
        $tenantId = $tenant->id;

        // Ensure default entrepot exists
        $stockService = app(StockService::class);
        $entrepotId = $stockService->getDefaultEntrepotId($tenantId);

        // Create or get test product
        $produit = Produit::firstOrCreate(
            ['reference' => 'POS-TEST-PROD-001', 'tenant_id' => $tenantId],
            [
                'designation' => 'Produit POS Test',
                'prix_ht_vente' => 100.00,
                'prix_ttc_vente' => 120.00,
                'taux_tva' => 20.00,
                'is_service' => false,
                'is_actif' => true,
                'stock_actuel' => 50,
            ]
        );

        // Ensure stock record exists
        $stock = Stock::firstOrCreate(
            ['produit_id' => $produit->id, 'entrepot_id' => $entrepotId, 'tenant_id' => $tenantId],
            ['quantite' => 50]
        );

        $initialStockQty = (float) $stock->quantite;

        // Execute fast-track checkout request
        $payload = [
            'lignes' => [
                [
                    'produit_id' => $produit->id,
                    'designation' => 'Produit POS Test',
                    'quantite' => 2,
                    'prix_unitaire' => 100.00,
                    'taux_tva' => 20.00,
                ]
            ],
            'mode_paiement' => 'especes',
            'montant_recu' => 300.00,
            'observations' => 'Test Fast-Track Checkout POS',
        ];

        $response = $this->actingAs($user)
            ->withHeaders(['X-Tenant-ID' => $tenantId])
            ->postJson('/api/pos/checkout', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'total_ttc' => 240.00,
                'monnaie' => 60.00,
            ]);

        $encodedId = $response->json('facture.id');
        $this->assertNotNull($encodedId);
        $rawFactureId = IdEncoder::decode($encodedId);

        // 1. Verify Facture created for Client Comptoir & marked as Paid
        $facture = Facture::find($rawFactureId);
        $this->assertNotNull($facture);
        $this->assertEquals(240.00, (float)$facture->total_ttc);
        $this->assertEquals(240.00, (float)$facture->montant_regle);
        $this->assertEquals(0, (float)$facture->montant_restant);
        $this->assertTrue((bool)$facture->est_reglee);

        // Verify Client Comptoir
        $this->assertEquals('COMPTOIR', $facture->client->code_client);

        // Verify payment morph relation created
        $this->assertCount(1, $facture->reglements);

        // 2. Verify Stock decremented by 2
        $stock->refresh();
        $this->assertEquals($initialStockQty - 2, (float)$stock->quantite);
    }
}
