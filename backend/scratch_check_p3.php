<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Produit;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('database.connections.tenant.database', 'geny_hp');
DB::purge('tenant');

$p = Produit::find(3);
if ($p) {
    echo "Produit 3: {$p->designation}\n";
    echo "Stock Actuel: {$p->stock_actuel}\n";
    echo "Seuil Alerte: {$p->seuil_alerte}\n";
    echo "Is Service: " . ($p->is_service ? 'YES' : 'NO') . "\n";
} else {
    echo "Produit 3 introuvable.\n";
}
