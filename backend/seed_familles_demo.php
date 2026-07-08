<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;
use App\Models\FamilleProduit;
use App\Models\Produit;

echo "🚀 Starting seeding of product families and sub-families...\n";

// Get all tenants from central database
$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo "\n>>> Seeding tenant: {$tenant->database_name} (ID: {$tenant->id})\n";
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');
    DB::reconnect('tenant');

    $tenantId = $tenant->id;

    // Define families and subfamilies
    $hierarchy = [
        'FILT' => [
            'libelle' => 'Filtration',
            'detail' => 'Filtres moteur, habitacle et carburant',
            'subs' => [
                'FIL-HUILE' => ['libelle' => 'Filtre à huile', 'detail' => 'Filtres à huile moteur'],
                'FIL-AIR'   => ['libelle' => 'Filtre à air', 'detail' => 'Filtres à air moteur'],
                'FIL-CARB'  => ['libelle' => 'Filtre à carburant', 'detail' => 'Filtres gasoil et essence'],
                'FIL-HAB'   => ['libelle' => 'Filtre d\'habitacle', 'detail' => 'Filtres à pollen et charbon actif']
            ]
        ],
        'FREIN' => [
            'libelle' => 'Freinage',
            'detail' => 'Composants du système de freinage',
            'subs' => [
                'FRN-PLAQ' => ['libelle' => 'Plaquettes', 'detail' => 'Plaquettes de frein avant et arrière'],
                'FRN-DISQ' => ['libelle' => 'Disques', 'detail' => 'Disques de frein ventilés et pleins'],
                'FRN-TAMB' => ['libelle' => 'Tambours', 'detail' => 'Tambours de frein'],
                'FRN-MACH' => ['libelle' => 'Mâchoires', 'detail' => 'Mâchoires de frein de stationnement / tambours'],
                'FRN-ETR'  => ['libelle' => 'Étriers', 'detail' => 'Étriers de frein'],
                'FRN-LIQ'  => ['libelle' => 'Liquide de frein', 'detail' => 'Liquides de frein DOT3, DOT4, DOT5.1']
            ]
        ]
    ];

    foreach ($hierarchy as $parentCode => $parentData) {
        // Create or update parent family
        $parent = FamilleProduit::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => $parentCode],
            [
                'libelle' => $parentData['libelle'],
                'detail' => $parentData['detail'],
                'parent_id' => null
            ]
        );
        echo "  - Family: {$parent->libelle} (ID: {$parent->id})\n";

        foreach ($parentData['subs'] as $subCode => $subData) {
            // Create or update sub-family
            $sub = FamilleProduit::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $subCode],
                [
                    'libelle' => $subData['libelle'],
                    'detail' => $subData['detail'],
                    'parent_id' => $parent->id
                ]
            );
            echo "    └─ Sub-family: {$sub->libelle} (ID: {$sub->id})\n";

            // Seed some demo products for each sub-family if they don't exist
            if ($subCode === 'FIL-HUILE') {
                Produit::updateOrCreate(
                    ['tenant_id' => $tenantId, 'reference' => 'FILT-H-BOSCH'],
                    [
                        'famille_id' => $sub->id,
                        'designation' => 'Filtre à huile Bosch P7078',
                        'marque' => 'Bosch',
                        'prix_ht_achat' => 45.00,
                        'prix_ht_vente' => 75.00,
                        'taux_tva' => 20.00,
                        'prix_ttc_vente' => 90.00,
                        'stock_actuel' => 25.00,
                        'stock_initial' => 25.00,
                        'seuil_alerte' => 5,
                        'unite' => 'Unité',
                        'is_service' => false,
                        'is_actif' => true
                    ]
                );
                Produit::updateOrCreate(
                    ['tenant_id' => $tenantId, 'reference' => 'FILT-H-VALEO'],
                    [
                        'famille_id' => $sub->id,
                        'designation' => 'Filtre à huile Valeo V506',
                        'marque' => 'Valeo',
                        'prix_ht_achat' => 38.00,
                        'prix_ht_vente' => 65.00,
                        'taux_tva' => 20.00,
                        'prix_ttc_vente' => 78.00,
                        'stock_actuel' => 40.00,
                        'stock_initial' => 40.00,
                        'seuil_alerte' => 10,
                        'unite' => 'Unité',
                        'is_service' => false,
                        'is_actif' => true
                    ]
                );
            } elseif ($subCode === 'FIL-AIR') {
                Produit::updateOrCreate(
                    ['tenant_id' => $tenantId, 'reference' => 'FILT-A-PURF'],
                    [
                        'famille_id' => $sub->id,
                        'designation' => 'Filtre à air Purflux A1204',
                        'marque' => 'Purflux',
                        'prix_ht_achat' => 85.00,
                        'prix_ht_vente' => 140.00,
                        'taux_tva' => 20.00,
                        'prix_ttc_vente' => 168.00,
                        'stock_actuel' => 12.00,
                        'stock_initial' => 12.00,
                        'seuil_alerte' => 3,
                        'unite' => 'Unité',
                        'is_service' => false,
                        'is_actif' => true
                    ]
                );
            } elseif ($subCode === 'FRN-PLAQ') {
                Produit::updateOrCreate(
                    ['tenant_id' => $tenantId, 'reference' => 'PLAQ-FRN-BREM'],
                    [
                        'famille_id' => $sub->id,
                        'designation' => 'Jeu de Plaquettes de frein Brembo P85020',
                        'marque' => 'Brembo',
                        'prix_ht_achat' => 220.00,
                        'prix_ht_vente' => 380.00,
                        'taux_tva' => 20.00,
                        'prix_ttc_vente' => 456.00,
                        'stock_actuel' => 15.00,
                        'stock_initial' => 15.00,
                        'seuil_alerte' => 4,
                        'unite' => 'Jeu',
                        'is_service' => false,
                        'is_actif' => true
                    ]
                );
            } elseif ($subCode === 'FRN-LIQ') {
                Produit::updateOrCreate(
                    ['tenant_id' => $tenantId, 'reference' => 'LIQ-FRN-CAST'],
                    [
                        'famille_id' => $sub->id,
                        'designation' => 'Liquide de frein Castrol DOT4 1L',
                        'marque' => 'Castrol',
                        'prix_ht_achat' => 60.00,
                        'prix_ht_vente' => 95.00,
                        'taux_tva' => 20.00,
                        'prix_ttc_vente' => 114.00,
                        'stock_actuel' => 30.00,
                        'stock_initial' => 30.00,
                        'seuil_alerte' => 8,
                        'unite' => 'Flacon',
                        'is_service' => false,
                        'is_actif' => true
                    ]
                );
            }
        }
    }
    echo "  ✅ Tenant {$tenant->database_name} seeded!\n";
}

echo "\n🎯 All seeding finished successfully!\n";
