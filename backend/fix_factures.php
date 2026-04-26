<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::all();

foreach ($tenants as $tenant) {
    if (!$tenant->database_name) continue;
    \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $tenant->database_name);
    \Illuminate\Support\Facades\DB::purge('tenant');
    
    App\Models\Facture::chunk(100, function($factures) {
        foreach ($factures as $f) {
            $f->montant_restant = max(0, $f->total_ttc - $f->montant_regle);
            if ($f->montant_restant <= 0 && $f->montant_regle > 0) {
                $f->est_reglee = true;
                $etatPaye = App\Models\EtatDocument::where('tenant_id', $f->tenant_id)->where('type_document', 'facture')->where('code', 'PAY')->first();
                if ($etatPaye) $f->etat_id = $etatPaye->id;
            } elseif ($f->montant_regle > 0) {
                $etatPartielle = App\Models\EtatDocument::where('tenant_id', $f->tenant_id)->where('type_document', 'facture')->where('code', 'PPY')->first();
                if ($etatPartielle) $f->etat_id = $etatPartielle->id;
            }
            $f->saveQuietly();
        }
    });

    App\Models\Client::chunk(50, function($clients) {
        foreach ($clients as $c) {
            $c->recalculerEncours();
        }
    });
}

echo "Correction terminee.\n";
