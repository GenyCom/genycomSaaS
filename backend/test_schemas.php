<?php
// Quick test: create a temp DB, run schemas 002-007, then drop it.
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

$testDb = 'genycom_test_validate_' . time();

echo "Creating test database: {$testDb}\n";
DB::connection('central')->statement("CREATE DATABASE `{$testDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

Config::set('database.connections.tenant.database', $testDb);
DB::purge('tenant');

$schemaPath = database_path('schema');
$files = collect(File::files($schemaPath))
    ->filter(fn($f) => preg_match('/^00[2-7]/', $f->getFilename()))
    ->sortBy(fn($f) => $f->getFilename());

$allOk = true;
foreach ($files as $file) {
    echo "Executing: {$file->getFilename()} ... ";
    try {
        DB::connection('tenant')->unprepared(File::get($file->getPathname()));
        echo "OK\n";
    } catch (\Exception $e) {
        echo "FAILED!\n";
        echo "  Error: " . $e->getMessage() . "\n\n";
        $allOk = false;
        break;
    }
}

if ($allOk) {
    echo "\n=== ALL 6 SCHEMA SCRIPTS PASSED SUCCESSFULLY ===\n";
}

// Cleanup
DB::statement("DROP DATABASE `{$testDb}`");
echo "Test database dropped.\n";
