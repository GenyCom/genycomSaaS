<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', 'geny_hp');
\Illuminate\Support\Facades\DB::purge('tenant');

$notif = \Illuminate\Support\Facades\DB::connection('tenant')->table('notifications')
    ->where('type', 'facture_overdue')
    ->orderBy('created_at', 'desc')
    ->first();

if ($notif) {
    echo "NOTIF FOUND: " . $notif->id . "\n";
    echo "DATA: " . $notif->data . "\n";
    echo "CREATED_AT: " . $notif->created_at . "\n";
} else {
    echo "Aucune notification de retard trouvée.\n";
}
