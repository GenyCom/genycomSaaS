<?php
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

foreach (Tenant::all() as $tenant) {
    Config::set('database.connections.tenant.database', $tenant->database_name);
    DB::purge('tenant');
    try {
        echo "Processing {$tenant->database_name}...\n";
        
        // Patch sequences_numerotation
        DB::connection('tenant')->statement("ALTER TABLE sequences_numerotation ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NOT NULL AFTER id");
        if (Schema::connection('tenant')->hasIndex('sequences_numerotation', 'uk_seq')) {
             DB::connection('tenant')->statement("ALTER TABLE sequences_numerotation DROP INDEX uk_seq");
        }
        DB::connection('tenant')->statement("ALTER TABLE sequences_numerotation ADD UNIQUE KEY IF NOT EXISTS uk_seq_tenant (tenant_id, type_document, prefixe, annee, mois)");
        
        // Patch entreprise
        DB::connection('tenant')->statement("ALTER TABLE entreprise ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NOT NULL AFTER id");
        
        // Patch etat_document
        DB::connection('tenant')->statement("ALTER TABLE etat_document ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NOT NULL AFTER id");
        if (Schema::connection('tenant')->hasIndex('etat_document', 'uk_etat_code')) {
             DB::connection('tenant')->statement("ALTER TABLE etat_document DROP INDEX uk_etat_code");
        }
        DB::connection('tenant')->statement("ALTER TABLE etat_document ADD UNIQUE KEY IF NOT EXISTS uk_etat_code_tenant (tenant_id, type_document, code)");

        echo "Successfully patched {$tenant->database_name}\n";
    } catch (\Exception $e) {
        echo "Error patching {$tenant->database_name}: " . $e->getMessage() . "\n";
    }
}
