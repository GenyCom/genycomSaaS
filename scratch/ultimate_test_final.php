<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BonCommandeFournisseur;
use App\Models\User;
use App\Services\ReceptionService;

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');

echo "--- TEST FINAL DE RECEPTION (JOMIA) ---\n";

$commande = BonCommandeFournisseur::with('lignes')->find(8);
if (!$commande) {
    echo "DEBUT: Commande #8 non trouvée.\n";
    exit;
}

echo "COMMANDE: {$commande->numero}\n";

$userId = User::where('id', '>', 0)->first()->id;

$receptionService = app(ReceptionService::class);

try {
    DB::beginTransaction();
    // Test de réception totale pour la commande #8
    $br = $receptionService->receptionnerCommande($commande, [], $userId);
    echo "[SUCCESS] BR créé: {$br->numero} (ID: {$br->id})\n";
    
    $lignes = $br->lignes;
    echo "Nombre de lignes BR créées: " . count($lignes) . "\n";
    foreach($lignes as $l) {
        echo " - Item: {$l->designation}, Qte: {$l->quantite_recue}\n";
    }
    
    DB::commit();
    echo "[FIN] Test réussi sans erreur d'intégrité.\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "[ERROR] Échec final: " . $e->getMessage() . "\n";
}
