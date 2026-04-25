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

$commande = BonCommandeFournisseur::with('lignes')->find(8);
if (!$commande) {
    echo "Commande #8 non trouvée.\n";
    exit;
}

echo "COMMANDE #8 TROUVEE: {$commande->numero}\n";

$userId = User::where('email', 'admin@jomia.com')->first()->id ?? 1;

$receptionService = app(ReceptionService::class);

try {
    $br = $receptionService->receptionnerCommande($commande, [], $userId);
    echo "[SUCCESS] BR créé avec succès: {$br->numero} (ID: {$br->id})\n";
} catch (\Throwable $e) {
    echo "[ERROR] Échec de la réception: " . $e->getMessage() . "\n";
}
