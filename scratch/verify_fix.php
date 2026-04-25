<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BonCommandeFournisseur;
use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'geny_txc']);
DB::purge('tenant');
DB::reconnect('tenant');

echo "Tentative de chargement du premier BCF...\n";
try {
    $bcf = BonCommandeFournisseur::first();
    if ($bcf) {
        echo "BCF #{$bcf->id} ({$bcf->numero}) chargé.\n";
        echo "Chargement des lignes...\n";
        $count = $bcf->lignes()->count();
        echo "Succès ! Nombre de lignes trouvées : {$count}\n";
    } else {
        echo "Aucun BCF trouvé dans geny_txc.\n";
    }
} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}
