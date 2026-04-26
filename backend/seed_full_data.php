<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Devis;
use App\Models\BonCommandeClient;
use App\Models\BonLivraison;
use App\Models\Facture;
use App\Models\Depense;
use App\Models\Reglement;
use App\Models\Projet;
use App\Models\EtatDocument;
use App\Models\FamilleProduit;
use App\Models\CategorieDepense;
use App\Models\User;

// Configuration du tenant cible
$tenantId = 4; // ID de geny_acer
$tenant = Tenant::find($tenantId);

if (!$tenant) {
    die("Tenant non trouvé !\n");
}

echo "--- Seeding complet pour le tenant: {$tenant->nom} ({$tenant->database_name}) ---\n";

// Switch connection
\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
\Illuminate\Support\Facades\Config::set('database.connections.tenant.username', $tenant->db_username ?? 'root');
\Illuminate\Support\Facades\Config::set('database.connections.tenant.password', $tenant->db_password ?? '');
DB::purge('tenant');

$tid = $tenantId;

DB::connection('tenant')->transaction(function () use ($tid) {
    
    // 1. Clients (12 clients)
    echo "Génération des Clients...\n";
    $clientsData = [
        ['societe' => 'Soremar SARL', 'ville' => 'Casablanca', 'type' => 'Société'],
        ['societe' => 'Techno Atlas', 'ville' => 'Rabat', 'type' => 'Société'],
        ['societe' => 'BTP Mansour', 'ville' => 'Marrakech', 'type' => 'Société'],
        ['societe' => 'Hicham El Fassi', 'ville' => 'Fès', 'type' => 'Physique'],
        ['societe' => 'Imprimerie du Nord', 'ville' => 'Tanger', 'type' => 'Société'],
        ['societe' => 'Agro Souss', 'ville' => 'Agadir', 'type' => 'Société'],
        ['societe' => 'Pharmacie Al Amal', 'ville' => 'Oujda', 'type' => 'Société'],
        ['societe' => 'Mounir Auto', 'ville' => 'Casablanca', 'type' => 'Physique'],
        ['societe' => 'Café de la Poste', 'ville' => 'Rabat', 'type' => 'Société'],
        ['societe' => 'Digital Sahara', 'ville' => 'Laayoune', 'type' => 'Société'],
        ['societe' => 'Zineb Bennani', 'ville' => 'Casablanca', 'type' => 'Physique'],
        ['societe' => 'Logistique Express', 'ville' => 'Meknès', 'type' => 'Société'],
    ];

    $clients = [];
    foreach ($clientsData as $idx => $c) {
        $clients[] = Client::create([
            'tenant_id' => $tid,
            'societe' => $c['societe'],
            'code_client' => 'C-'.str_pad($idx+1, 4, '0', STR_PAD_LEFT),
            'email' => strtolower(str_replace(' ', '.', $c['societe'])).'@example.com',
            'telephone' => '0522'.rand(100000, 999999),
            'ville' => $c['ville'],
            'adresse' => 'Quartier Industriel, ' . $c['ville'],
            'type_client' => $c['type'],
        ]);
    }

    // 2. Fournisseurs (6 fournisseurs)
    echo "Génération des Fournisseurs...\n";
    $fournisseursData = [
        ['societe' => 'Maroc Bureau', 'cat' => 'Mobilier'],
        ['societe' => 'ElectroPlanet Pro', 'cat' => 'Informatique'],
        ['societe' => 'Afriquia Gaz', 'cat' => 'Énergie'],
        ['societe' => 'Ciments du Maroc', 'cat' => 'BTP'],
        ['societe' => 'Inwi Business', 'cat' => 'Télécom'],
        ['societe' => 'Office Fournitures', 'cat' => 'Papeterie'],
    ];

    foreach ($fournisseursData as $idx => $f) {
        Fournisseur::create([
            'tenant_id' => $tid,
            'societe' => $f['societe'],
            'code_fournisseur' => 'F-'.str_pad($idx+1, 4, '0', STR_PAD_LEFT),
            'email' => 'contact@'.strtolower(str_replace(' ', '', $f['societe'])).'.ma',
            'ville' => 'Casablanca',
        ]);
    }

    // 3. Produits (20 produits)
    echo "Génération des Produits...\n";
    $famille = FamilleProduit::first();
    $familleId = $famille ? $famille->id : null;

    $produitsData = [
        ['ref' => 'PC-DELL-5570', 'nom' => 'Ordinateur Dell Latitude 5570', 'prix' => 7500],
        ['ref' => 'PRN-HP-M404', 'nom' => 'Imprimante HP Laserjet M404n', 'prix' => 2800],
        ['ref' => 'TON-HP-59A', 'nom' => 'Toner HP 59A Noir', 'prix' => 1250],
        ['ref' => 'PPR-A4-80G', 'nom' => 'Papier A4 80g Double A (Rame)', 'prix' => 65],
        ['ref' => 'DSK-EXT-1TB', 'nom' => 'Disque Dur Externe 1To WD', 'prix' => 550],
        ['ref' => 'CHAIR-OFF-B', 'nom' => 'Chaise Bureau Ergonomique Noire', 'prix' => 1450],
        ['ref' => 'TBL-CONF-2M', 'nom' => 'Table de Conférence 2m Bois', 'prix' => 3200],
        ['ref' => 'SCR-SAM-24', 'nom' => 'Ecran Samsung 24 pouces LED', 'prix' => 1650],
        ['ref' => 'KBD-LOGI-K120', 'nom' => 'Clavier Logitech K120 USB', 'prix' => 120],
        ['ref' => 'MSE-LOGI-M90', 'nom' => 'Souris Logitech M90 Filaire', 'prix' => 85],
        ['ref' => 'UPS-APC-700', 'nom' => 'Onduleur APC 700VA', 'prix' => 950],
        ['ref' => 'CAB-HDMI-3M', 'nom' => 'Câble HDMI 3m Haute Qualité', 'prix' => 75],
        ['ref' => 'SERV-INST-PC', 'nom' => 'Installation & Configuration PC', 'prix' => 350, 'service' => 1],
        ['ref' => 'SERV-MAINT-AN', 'nom' => 'Contrat Maintenance Annuel', 'prix' => 5000, 'service' => 1],
        ['ref' => 'HUB-USB-4P', 'nom' => 'Hub USB 4 Ports', 'prix' => 180],
        ['ref' => 'CAM-LOGI-C920', 'nom' => 'Webcam Logitech C920 HD', 'prix' => 890],
        ['ref' => 'WIFI-TP-LINK', 'nom' => 'Routeur WiFi TP-Link AC1200', 'prix' => 450],
        ['ref' => 'HDD-SSD-500G', 'nom' => 'Disque SSD 500Go Crucial', 'prix' => 620],
        ['ref' => 'RAM-8GB-DDR4', 'nom' => 'Mémoire RAM 8Go DDR4 3200MHz', 'prix' => 380],
        ['ref' => 'BAG-LAPTOP-15', 'nom' => 'Sacoche PC Portable 15.6"', 'prix' => 250],
    ];

    $produits = [];
    foreach ($produitsData as $p) {
        $produits[] = Produit::create([
            'tenant_id' => $tid,
            'reference' => $p['ref'],
            'designation' => $p['nom'],
            'prix_ht_vente' => $p['prix'],
            'prix_ht_achat' => $p['prix'] * 0.7,
            'is_service' => $p['service'] ?? 0,
            'famille_id' => $familleId,
            'stock_actuel' => ($p['service'] ?? 0) ? 0 : rand(5, 50),
            'seuil_alerte' => 5,
        ]);
    }

    // 4. Projets (3 projets)
    echo "Génération des Projets...\n";
    $projets = [
        Projet::create(['tenant_id' => $tid, 'code_projet' => 'PRJ-001', 'nom_projet' => 'Refonte Informatique Soremar', 'client_id' => $clients[0]->id, 'statut' => 'en_cours']),
        Projet::create(['tenant_id' => $tid, 'code_projet' => 'PRJ-002', 'nom_projet' => 'Installation Réseau Atlas', 'client_id' => $clients[1]->id, 'statut' => 'termine']),
        Projet::create(['tenant_id' => $tid, 'code_projet' => 'PRJ-003', 'nom_projet' => 'Maintenance Parc BTP', 'client_id' => $clients[2]->id, 'statut' => 'en_cours']),
    ];

    // 5. Devis & Factures (Workflow complet)
    echo "Génération des Devis et Factures...\n";
    $etatsDevis = EtatDocument::where('type_document', 'devis')->get();
    $etatsFacture = EtatDocument::where('type_document', 'facture')->get();

    for ($i = 0; $i < 10; $i++) {
        $client = $clients[array_rand($clients)];
        $devis = Devis::create([
            'tenant_id' => $tid,
            'numero' => 'DV-'.date('Ym').'-'.str_pad($i+1, 4, '0', STR_PAD_LEFT),
            'date_devis' => now()->subDays(rand(1, 30)),
            'client_id' => $client->id,
            'etat_id' => $etatsDevis->random()->id,
            'total_ht' => 0, 'total_tva' => 0, 'total_ttc' => 0,
        ]);

        // Lignes
        $totalHT = 0;
        for ($j = 0; $j < rand(1, 4); $j++) {
            $p = $produits[array_rand($produits)];
            $qte = rand(1, 5);
            $montantHT = $qte * $p->prix_ht_vente;
            $montantTVA = $montantHT * 0.2;
            
            DB::connection('tenant')->table('ligne_devis')->insert([
                'tenant_id' => $tid,
                'devis_id' => $devis->id,
                'produit_id' => $p->id,
                'designation' => $p->designation,
                'quantite' => $qte,
                'prix_unitaire' => $p->prix_ht_vente,
                'taux_tva' => 20,
                'montant_ht' => $montantHT,
                'montant_tva' => $montantTVA,
                'montant_ttc' => $montantHT + $montantTVA,
                'ordre' => $j + 1,
            ]);
            $totalHT += $montantHT;
        }
        $devis->update([
            'total_ht' => $totalHT,
            'total_tva' => $totalHT * 0.2,
            'total_ttc' => $totalHT * 1.2,
        ]);

        // Si le devis est accepté, on crée une facture
        $accepteId = $etatsDevis->where('code', 'ACC')->first()?->id;
        if ($devis->etat_id == $accepteId || rand(0, 1)) {
            $facture = Facture::create([
                'tenant_id' => $tid,
                'numero' => 'FA-'.date('Ym').'-'.str_pad($i+1, 4, '0', STR_PAD_LEFT),
                'date_facture' => $devis->date_devis->addDays(2),
                'client_id' => $client->id,
                'devis_id' => $devis->id,
                'etat_id' => $etatsFacture->where('code', 'OVR')->first()?->id,
                'total_ht' => $devis->total_ht,
                'total_tva' => $devis->total_tva,
                'total_ttc' => $devis->total_ttc,
                'montant_restant' => $devis->total_ttc,
            ]);

            foreach (DB::connection('tenant')->table('ligne_devis')->where('devis_id', $devis->id)->get() as $ld) {
                DB::connection('tenant')->table('ligne_facture')->insert([
                    'tenant_id' => $tid,
                    'facture_id' => $facture->id,
                    'produit_id' => $ld->produit_id,
                    'designation' => $ld->designation,
                    'quantite' => $ld->quantite,
                    'prix_unitaire' => $ld->prix_unitaire,
                    'taux_tva' => 20,
                    'montant_ht' => $ld->montant_ht,
                    'montant_tva' => $ld->montant_tva,
                    'montant_ttc' => $ld->montant_ttc,
                    'ordre' => $ld->ordre,
                ]);
            }

            // Un petit règlement pour certaines factures
            if (rand(0, 1)) {
                $paye = rand(100, (int)$facture->total_ttc);
                Reglement::create([
                    'tenant_id' => $tid,
                    'payable_id' => $facture->id,
                    'payable_type' => 'App\\Models\\Facture',
                    'montant' => $paye,
                    'date_reglement' => now(),
                    'mode_reglement_id' => 1, // Espèces
                ]);
                $facture->update([
                    'montant_regle' => $paye,
                    'montant_restant' => $facture->total_ttc - $paye,
                    'est_reglee' => $paye >= $facture->total_ttc ? 1 : 0,
                    'etat_id' => $paye >= $facture->total_ttc ? $etatsFacture->where('code', 'PAY')->first()->id : $facture->etat_id,
                ]);
            }
        }
    }

    // 6. Dépenses (10 lignes)
    echo "Génération des Dépenses...\n";
    $catsDepense = CategorieDepense::all();
    $etatsDepense = EtatDocument::where('type_document', 'depense')->get();

    for ($i = 0; $i < 10; $i++) {
        Depense::create([
            'tenant_id' => $tid,
            'libelle' => 'Dépense Diverse #' . ($i+1),
            'montant' => rand(50, 2000),
            'date_depense' => now()->subDays(rand(1, 60)),
            'categorie_id' => $catsDepense->random()->id,
            'etat_id' => $etatsDepense->random()->id,
        ]);
    }

    echo "--- Terminé avec succès ! ---\n";
});
