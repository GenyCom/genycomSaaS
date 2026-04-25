<?php
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
use App\Services\NumerotationService;
use App\Models\EtatDocument;

function run_test() {
    echo "--- DEBUT DU TEST WORKFLOW BIZ ---\n";
    
    $tenant = Tenant::first();
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');
    
    $user = User::where('email', 'admin@admin.com')->first() ?: User::first();
    auth()->login($user);

    // 1. VENTE
    echo "[1/2] Test Cycle Vente...\n";
    $client = Client::first();
    $produit = Produit::first();
    
    $devis = Devis::create([
        'tenant_id' => $tenant->id,
        'numero' => 'TEST-DEV-' . time(),
        'date_devis' => now(),
        'client_id' => $client->id,
        'total_ht' => 100,
        'total_tva' => 20,
        'total_ttc' => 120,
        'created_by' => $user->id
    ]);
    
    // Simuler transformation via le controller (ou appeler le controlleur directement)
    $controller = app(\App\Http\Controllers\Api\WorkflowVenteController::class);
    $req = new \Illuminate\Http\Request();
    $req->merge(['current_tenant' => $tenant]);
    
    $res = $controller->devisToBC($req, $devis);
    $bccId = $res->getData()->id;
    echo "  - Devis -> BCC OK (ID: $bccId)\n";
    
    $bcc = BonCommandeClient::find($bccId);
    $res = $controller->bcToBL($req, $bcc);
    $blId = $res->getData()->id;
    echo "  - BCC -> BL OK (ID: $blId)\n";
    
    $bl = BonLivraison::find($blId);
    $res = $controller->blToFacture($req, $bl);
    $factId = $res->getData()->id;
    echo "  - BL -> Facture OK (ID: $factId)\n";

    // 2. ACHAT
    echo "[2/2] Test Cycle Achat...\n";
    $fourn = Fournisseur::first();
    $bcf = BCF::create([
        'tenant_id' => $tenant->id,
        'numero' => 'TEST-BCF-' . time(),
        'date_commande' => now(),
        'fournisseur_id' => $fourn->id,
        'total_ht' => 500,
        'total_tva' => 100,
        'total_ttc' => 600,
        'statut' => 'valide',
        'created_by' => $user->id
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
    
    $achatController = app(\App\Http\Controllers\Api\WorkflowAchatController::class);
    $res = $achatController->commandeToBR($req, $bcf->id);
    if ($res->getStatusCode() !== 200) {
        throw new \Exception("Erreur BCF -> BR: " . $res->getData()->message);
    }
    $brId = $res->getData()->id;
    echo "  - BCF -> BR OK (ID: $brId)\n";
    
    $reqFac = new \Illuminate\Http\Request();
    $reqFac->replace(['br_ids' => [$brId]]);
    $reqFac->merge(['current_tenant' => $tenant]);
    $res = $achatController->brToFacture($reqFac);
    if ($res->getStatusCode() !== 200) {
        throw new \Exception("Erreur BR -> Facture Achat: " . $res->getData()->message);
    }
    $facAchatId = $res->getData()->id;
    echo "  - BR -> Facture Achat OK (ID: $facAchatId)\n";

    echo "--- TEST TERMINE AVEC SUCCES ---\n";
}

try {
    run_test();
} catch (\Throwable $e) {
    echo "❌ TEST ECHOUE : " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
