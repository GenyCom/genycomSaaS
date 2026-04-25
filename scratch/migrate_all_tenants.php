<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $dbs = DB::select('SHOW DATABASES');
    $tenantDbs = [];
    foreach ($dbs as $db) {
        $name = $db->Database;
        // Search for databases that look like tenant DBs (e.g. geny_*)
        if (strpos($name, 'geny_') === 0 && $name !== 'genycom_central') {
            $tenantDbs[] = $name;
        }
    }

    if (empty($tenantDbs)) {
        echo "No tenant databases found starting with 'geny_'.\n";
        // Check if there are ANY other DBs
        foreach($dbs as $db) echo "- " . $db->Database . "\n";
        exit;
    }

    foreach ($tenantDbs as $dbName) {
        echo "Processing database: $dbName\n";
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');
        $conn = DB::connection('tenant');

        echo "- Updating 'devise' table...\n";
        if (!Schema::connection('tenant')->hasColumn('devise', 'tenant_id')) {
            $conn->statement("ALTER TABLE devise ADD COLUMN tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER id");
        }

        echo "- Updating 'projets' table...\n";
        if (!Schema::connection('tenant')->hasColumn('projets', 'devise_id')) {
            $conn->statement("ALTER TABLE projets ADD COLUMN devise_id BIGINT UNSIGNED NULL AFTER budget_consomme");
            $conn->statement("ALTER TABLE projets ADD COLUMN taux_change_document DECIMAL(24,6) DEFAULT 1.000000 AFTER devise_id");
            $conn->statement("ALTER TABLE projets ADD CONSTRAINT fk_projet_devise FOREIGN KEY (devise_id) REFERENCES devise(id)");
        }

        echo "- Initializing existing projects...\n";
        $devisePrincipale = $conn->table('devise')->where('is_principale', true)->first();
        if ($devisePrincipale) {
            $conn->table('projets')->whereNull('devise_id')->update([
                'devise_id' => $devisePrincipale->id,
                'taux_change_document' => 1.0
            ]);
        }
    }
    echo "SUCCESS!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
