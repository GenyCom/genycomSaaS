<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $existing = DB::connection('central')
        ->table('permissions')
        ->where('name', 'stock.initialisation_complete')
        ->first();

    if (!$existing) {
        $permId = DB::connection('central')->table('permissions')->insertGetId([
            'name'         => 'stock.initialisation_complete',
            'display_name' => 'Initialisation complète du stock',
            'module'       => 'stock',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        echo "Permission 'stock.initialisation_complete' créée avec l'ID {$permId}.\n";
    } else {
        $permId = $existing->id;
        echo "Permission 'stock.initialisation_complete' existe déjà (ID {$permId}).\n";
    }

    // Assigner la permission à tous les rôles administrateurs existants dans la base centrale
    $roles = DB::connection('central')->table('roles')->get();
    foreach ($roles as $role) {
        $alreadyAssigned = DB::connection('central')
            ->table('permission_role')
            ->where('role_id', $role->id)
            ->where('permission_id', $permId)
            ->exists();

        if (!$alreadyAssigned && (strtolower($role->name) === 'admin' || strtolower($role->name) === 'administrateur' || $role->is_system)) {
            DB::connection('central')->table('permission_role')->insert([
                'role_id'       => $role->id,
                'permission_id' => $permId,
            ]);
            echo "Permission attribuée au rôle '{$role->name}' (ID {$role->id}).\n";
        }
    }
} catch (\Exception $e) {
    echo "Erreur lors de l'exécution du patch : " . $e->getMessage() . "\n";
}
