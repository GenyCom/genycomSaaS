<?php

use App\Models\Entrepot;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

$basePath = dirname(__DIR__);
require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Créer l'entrepôt par défaut si inexistant
$entrepot = Entrepot::firstOrCreate(
    ['code' => 'DEP-PPAL', 'tenant_id' => 1],
    ['nom' => 'Dépôt Principal', 'is_default' => true]
);

echo "Entrepôt créé/trouvé: {$entrepot->nom} (ID: {$entrepot->id})\n";

// 2. Rattacher le stock NULL à cet entrepôt
$affected = Stock::whereNull('entrepot_id')->update(['entrepot_id' => $entrepot->id]);

echo "Stock mis à jour: {$affected} lignes corrigées.\n";
