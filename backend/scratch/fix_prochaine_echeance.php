<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

$tenants = DB::connection('central')->table('tenants')->get();

foreach ($tenants as $tenant) {
    echo "Processing Tenant: {$tenant->nom}\n";
    
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    
    try {
        Schema::connection('tenant')->table('contrats', function (Blueprint $table) {
            // Ajout des colonnes manquantes identifiées dans les logs
            if (!Schema::connection('tenant')->hasColumn('contrats', 'prochaine_echeance')) {
                $table->date('prochaine_echeance')->nullable()->after('date_fin');
            }
            if (!Schema::connection('tenant')->hasColumn('contrats', 'prochaine_facture')) {
                $table->date('prochaine_facture')->nullable()->after('prochaine_echeance');
            }
            if (!Schema::connection('tenant')->hasColumn('contrats', 'observations')) {
                $table->text('observations')->nullable()->after('statut');
            }
            if (!Schema::connection('tenant')->hasColumn('contrats', 'numero')) {
                $table->string('numero', 50)->nullable()->after('id');
            }
            if (!Schema::connection('tenant')->hasColumn('contrats', 'frequence')) {
                $table->string('frequence')->nullable()->after('frequence_facturation');
            }
            if (!Schema::connection('tenant')->hasColumn('contrats', 'total_ht')) {
                $table->decimal('total_ht', 24, 2)->default(0)->after('montant_ht');
            }
            if (!Schema::connection('tenant')->hasColumn('contrats', 'total_tva')) {
                $table->decimal('total_tva', 24, 2)->default(0)->after('total_ht');
            }
            if (!Schema::connection('tenant')->hasColumn('contrats', 'total_ttc')) {
                $table->decimal('total_ttc', 24, 2)->default(0)->after('total_tva');
            }
        });

        echo "  ✔ Table 'contrats' updated successfully.\n";
    } catch (\Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}
