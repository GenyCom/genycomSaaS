<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Projet;
use App\Models\Devise;
use App\Models\TauxTva;
use Illuminate\Support\Facades\Config;

echo "🚀 Starting High-Quality Expert Seeding...\n";

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo ">>> Seeding tenant: {$tenant->database_name}\n";
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');
    DB::reconnect('tenant');

    $tenantId = $tenant->id;

    // 1. Clients
    echo "  - Creating Expert Clients...\n";
    Client::updateOrCreate(
        ['code_client' => 'C-SOREC'],
        [
            'societe' => 'SOREC MAROC',
            'is_personne_physique' => false,
            'ice' => '001234567890012',
            'if_fiscal' => '45678912',
            'rc' => 'RC-9988-CAS',
            'patente' => 'PAT-777',
            'adresse' => 'Quartier Palmier, Casablanca',
            'ville' => 'Casablanca',
            'pays' => 'Maroc',
            'telephone' => '+212 522 00 11 22',
            'email' => 'contact@sorec.ma',
            'rib' => '011 780 00000 12345678988 55',
            'is_active' => true
        ]
    );

    Client::updateOrCreate(
        ['code_client' => 'C-DUPONT'],
        [
            'societe' => 'Jean Dupont',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'is_personne_physique' => true,
            'adresse' => 'Boulevard Hassan II, Rabat',
            'ville' => 'Rabat',
            'telephone' => '+212 661 00 22 33',
            'email' => 'j.dupont@gmail.com',
            'is_active' => true
        ]
    );

    // 2. Suppliers
    echo "  - Creating Expert Suppliers...\n";
    Fournisseur::updateOrCreate(
        ['societe' => 'Cisco Systems'],
        [
            'ice' => '002223334440055',
            'if_fiscal' => '77889900',
            'adresse' => 'Silicon Valley, CA, USA',
            'email' => 'support@cisco.com',
            'delai_livraison' => 15,
            'is_active' => true
        ]
    );

    // 3. Products
    echo "  - Creating Expert Products...\n";
    Produit::updateOrCreate(
        ['reference' => 'SRV-DELL-540'],
        [
            'designation' => 'Serveur Dell PowerEdge R540',
            'marque' => 'Dell',
            'type_produit' => 'bien',
            'is_service' => false,
            'prix_ht_achat' => 12000,
            'marge_pourcentage' => 25,
            'prix_ht_vente' => 15000,
            'taux_tva' => 20,
            'prix_ttc_vente' => 18000,
            'stock_actuel' => 10,
            'stock_min' => 2,
            'unite' => 'Unité',
            'poids' => 15.5,
            'dimensions' => '2U Rack',
            'garantie_mois' => 36,
            'is_actif' => true
        ]
    );

    Produit::updateOrCreate(
        ['reference' => 'SRV-AUDIT'],
        [
            'designation' => 'Audit Sécurité Infrastructure',
            'type_produit' => 'service',
            'is_service' => true,
            'prix_ht_vente' => 8000,
            'taux_tva' => 20,
            'prix_ttc_vente' => 9600,
            'unite' => 'Forfait',
            'is_actif' => true
        ]
    );

    // 4. Projects
    echo "  - Creating Expert Projects...\n";
    $c = Client::where('code_client', 'C-SOREC')->first();
    Projet::updateOrCreate(
        ['code_projet' => 'PRJ-24-001'],
        [
            'nom_projet' => 'Migration Cloud 2024',
            'client_id' => $c->id,
            'type_projet' => 'Installation',
            'date_debut' => '2024-01-01',
            'date_fin_prevue' => '2024-12-31',
            'budget_prevu' => 50000,
            'statut' => 'en_cours',
            'avancement_pcent' => 45,
            'priorite' => 'haute'
        ]
    );

    echo "  ✅ Tenant {$tenant->database_name} seeded!\n";
}

echo "\n🎯 All expert seeding completed successfully!\n";
