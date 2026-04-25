<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', 'geny_hp');
$s = \App\Models\Stock::with(['produit', 'entrepot'])->find(4);
echo json_encode($s, JSON_PRETTY_PRINT);
echo "\nMOVEMENTS COUNT: " . \App\Models\MouvementStock::where('produit_id', $s->produit_id)->count();
