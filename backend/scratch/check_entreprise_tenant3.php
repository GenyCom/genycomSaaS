<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

$tenant = Tenant::find(3);
if (!$tenant) {
    echo "Tenant 3 not found.\n";
    exit;
}

echo "Configuring tenant connection for: " . $tenant->database_name . "\n";
$tenant->configure();

try {
    $entreprise = DB::connection('tenant')->table('entreprise')->first();
    if ($entreprise) {
        echo "Entreprise found:\n";
        print_r($entreprise);
    } else {
        echo "No entry found in 'entreprise' table.\n";
        
        // Let's see if we can insert a default one for demo
        echo "Inserting default entreprise for demo...\n";
        DB::connection('tenant')->table('entreprise')->insert([
            'raison_sociale' => 'GenyCom Demo SARL',
            'email' => 'contact@genycom.com',
            'ville' => 'Casablanca',
            'pays' => 'Maroc',
            'logo_path' => 'https://saas.genycom.com/logo.png', // Default logo
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Done.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
