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
    $p->update(['seuil_alerte' => 50]);
    echo "Seuil du produit 3 mis à jour à 50.\n";
}
