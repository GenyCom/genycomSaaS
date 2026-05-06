<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

echo "=== Applying 014_fix_missing_columns.sql to all tenants ===\n\n";

$sql = file_get_contents(__DIR__ . '/../database/schema/014_fix_missing_columns.sql');

foreach (Tenant::all() as $tenant) {
    echo "--- Tenant {$tenant->id} ({$tenant->nom}) => {$tenant->database_name} ---\n";
    
    try {
        $tenant->configure();
        DB::connection('tenant')->unprepared($sql);
        echo "  ✔ Columns added successfully.\n";
        
        // Verify
        $columns_bcf = DB::connection('tenant')->select("SHOW COLUMNS FROM bcf WHERE Field IN ('projet_id', 'entrepot_id')");
        echo "  BCF columns: " . implode(', ', array_map(fn($c) => $c->Field, $columns_bcf)) . "\n";
        
        $columns_bcc = DB::connection('tenant')->select("SHOW COLUMNS FROM bons_commande_client WHERE Field = 'entrepot_id'");
        echo "  BCC entrepot_id: " . (count($columns_bcc) > 0 ? 'EXISTS' : 'MISSING') . "\n";
        
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "=== Done ===\n";
