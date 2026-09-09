<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PATCH DATABASE : NULLABLE OWNER ROLE_ID ===\n";

try {
    // 1. Modifier la colonne role_id dans tenant_user pour qu'elle accepte NULL
    DB::connection('central')->statement("
        ALTER TABLE tenant_user MODIFY role_id BIGINT UNSIGNED NULL;
    ");
    echo "✅ Colonne 'role_id' dans 'tenant_user' modifiée vers NULLABLE.\n";

    // 2. Mettre à jour tous les comptes Owner pour passer role_id à NULL
    $affected = DB::connection('central')->table('tenant_user')
        ->where('is_owner', 1)
        ->update(['role_id' => null]);

    echo "✅ Les comptes gérants (is_owner = 1) ont été mis à jour avec role_id = NULL ({$affected} ligne(s) modifiée(s)).\n";

    echo "=== PATCH TERMINÉ AVEC SUCCÈS ===\n";

} catch (\Throwable $e) {
    echo "❌ EXCEPTION : " . $e->getMessage() . "\n";
    exit(1);
}
