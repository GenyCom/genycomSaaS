<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Facture;
use App\Models\Client;
use App\Services\PosClotureService;

class PosClotureTest extends TestCase
{
    public function test_pos_cloture_summary_and_execution()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $tenant = $user->tenants()->first() ?? Tenant::first();
        if (!$tenant) {
            $this->markTestSkipped('No tenant found');
        }

        $tenant->configure();
        $tenantId = $tenant->id;

        // Ensure table exists
        $service = app(PosClotureService::class);
        $service->ensureTableExists();

        // 1. Test GET current session summary
        $response = $this->actingAs($user)
            ->withHeaders(['X-Tenant-ID' => $tenantId])
            ->getJson('/api/pos/cloture/current');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'date', 'caissier', 'opened_at', 'closed_at',
                'fond_initial', 'total_ventes', 'total_especes',
                'total_carte', 'total_attendu', 'detail_paiements',
            ]);

        // 2. Test POST cloture
        $cloturePayload = [
            'fond_initial'  => 500.00,
            'total_declare' => 480.00,
            'observations'  => 'Test Clôture Z',
        ];

        $closeResponse = $this->actingAs($user)
            ->withHeaders(['X-Tenant-ID' => $tenantId])
            ->postJson('/api/pos/cloture', $cloturePayload);

        $closeResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $rapportTexte = $closeResponse->json('rapport_texte');
        $this->assertStringContainsString('RAPPORT DE CLOTURE Z', $rapportTexte);
        $this->assertStringContainsString('1. RÉCAPITULATIF DES VENTES', $rapportTexte);
        $this->assertStringContainsString('2. DÉTAIL PAR PAIEMENT', $rapportTexte);
        $this->assertStringContainsString('3. CONTRÔLE TIROIR-CAISSE', $rapportTexte);
        $this->assertStringContainsString('ECART', $rapportTexte);

        // 3. Test GET cloture history
        $histResponse = $this->actingAs($user)
            ->withHeaders(['X-Tenant-ID' => $tenantId])
            ->getJson('/api/pos/cloture/history');

        $histResponse->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($histResponse->json()));
    }
}
