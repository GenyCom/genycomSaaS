<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['database.connections.tenant.database' => 'geny_jomia']);
DB::purge('tenant');

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    $conn = DB::connection('tenant');
    
    echo "Updating 'devise' table...\n";
    if (!Schema::connection('tenant')->hasColumn('devise', 'tenant_id')) {
        $conn->statement("ALTER TABLE devise ADD COLUMN tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER id");
        echo "- Added tenant_id to devise\n";
    }

    echo "Updating 'projets' table...\n";
    if (!Schema::connection('tenant')->hasColumn('projets', 'devise_id')) {
        $conn->statement("ALTER TABLE projets ADD COLUMN devise_id BIGINT UNSIGNED NULL AFTER budget_consomme");
        $conn->statement("ALTER TABLE projets ADD COLUMN taux_change_document DECIMAL(24,6) DEFAULT 1.000000 AFTER devise_id");
        $conn->statement("ALTER TABLE projets ADD CONSTRAINT fk_projet_devise FOREIGN KEY (devise_id) REFERENCES devise(id)");
        echo "- Added currency columns to projets\n";
    }

    echo "Initializing existing projects...\n";
    $devisePrincipale = $conn->table('devise')->where('is_principale', true)->first();
    if ($devisePrincipale) {
        $conn->table('projets')->whereNull('devise_id')->update([
            'devise_id' => $devisePrincipale->id,
            'taux_change_document' => 1.0
        ]);
        echo "- Initialized existing projects with default currency (ID: {$devisePrincipale->id})\n";
    }

    echo "SUCCESS!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
