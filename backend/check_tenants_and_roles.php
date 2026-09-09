<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tenants = DB::connection('central')->table('tenants')->get();
echo "TENANTS:\n";
foreach ($tenants as $t) {
    echo "Tenant ID: {$t->id} | Nom: {$t->nom} | DB: {$t->database_name}\n";
}

$roles = DB::connection('central')->table('roles')->get();
echo "\nROLES:\n";
foreach ($roles as $r) {
    echo "Role ID: {$r->id} | Tenant ID: " . ($r->tenant_id ?? 'NULL') . " | Name: {$r->name} | Is System: {$r->is_system}\n";
}

$tenantUsers = DB::connection('central')->table('tenant_user')->get();
echo "\nTENANT USERS:\n";
foreach ($tenantUsers as $tu) {
    echo "TenantUser ID: {$tu->id} | Tenant ID: {$tu->tenant_id} | User ID: {$tu->user_id} | Role ID: {$tu->role_id} | Is Owner: {$tu->is_owner}\n";
}
