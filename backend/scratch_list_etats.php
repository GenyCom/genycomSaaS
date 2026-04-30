<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$results = DB::select("SELECT id, tenant_id, type_document, code FROM etat_document");
foreach($results as $r) {
    echo "ID: {$r->id} | Tenant: {$r->tenant_id} | Type: {$r->type_document} | Code: {$r->code}\n";
}
