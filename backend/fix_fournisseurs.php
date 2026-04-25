<?php
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = Tenant::all();
foreach ($tenants as $t) {
    echo "Updating database: {$t->database_name}...\n";
    Config::set('database.connections.tenant.database', $t->database_name);
    DB::purge('tenant');
    
    try {
        DB::connection('tenant')->statement("
            ALTER TABLE fournisseurs 
            ADD COLUMN IF NOT EXISTS is_personne_physique TINYINT(1) DEFAULT 0 AFTER societe, 
            ADD COLUMN IF NOT EXISTS civilite VARCHAR(10) NULL AFTER is_personne_physique, 
            ADD COLUMN IF NOT EXISTS nom VARCHAR(100) NULL AFTER civilite, 
            ADD COLUMN IF NOT EXISTS prenom VARCHAR(100) NULL AFTER nom, 
            ADD COLUMN IF NOT EXISTS fax VARCHAR(20) NULL AFTER mobile, 
            ADD COLUMN IF NOT EXISTS site_web VARCHAR(255) NULL AFTER email, 
            ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) NULL AFTER banque, 
            ADD COLUMN IF NOT EXISTS observations LONGTEXT NULL AFTER image_path
        ");
        echo "✅ Done for {$t->database_name}.\n";
    } catch (\Exception $e) {
        echo "❌ Error for {$t->database_name}: " . $e->getMessage() . "\n";
    }
}
