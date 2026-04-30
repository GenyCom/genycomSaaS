<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tenant = \App\Models\Tenant::find(3);
echo "Tenant 3 Database: " . $tenant->database_name . "\n";

config(['database.connections.tenant.database' => $tenant->database_name]);
DB::purge('tenant');

$columns = Schema::connection('tenant')->getColumnListing('contrats');
echo "Columns in 'contrats':\n";
print_r($columns);

if (in_array('prochaine_echeance', $columns)) {
    echo "✔ Column 'prochaine_echeance' EXISTS.\n";
} else {
    echo "❌ Column 'prochaine_echeance' IS MISSING.\n";
}
