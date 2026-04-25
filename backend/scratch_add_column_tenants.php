<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

$tenants = Tenant::where('statut', 'actif')->get();

foreach ($tenants as $tenant) {
    echo "Traitement du locataire : {$tenant->nom} ({$tenant->database_name})...\n";
    
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');

    Schema::connection('tenant')->table('depenses', function(Blueprint $table) use ($tenant) {
        if (!Schema::connection('tenant')->hasColumn('depenses', 'derniere_notification_at')) {
            $table->timestamp('derniere_notification_at')->nullable();
            echo "✔ Colonne ajoutée pour {$tenant->nom}\n";
        } else {
            echo "ℹ Colonne déjà présente pour {$tenant->nom}\n";
        }
    });
}
