<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.connections.tenant.database' => 'geny_hp']);
DB::purge('tenant');

$conn = DB::connection('tenant');

echo "Cleaning 'geny_hp' database...\n";

$legacyTables = [
    'ligne_bon_commande_fournisseur', 'bons_commande_fournisseur',
    'ligne_bon_reception', 'bons_reception',
    'dettes_fournisseur',
    'ligne_avoir_fournisseur', 'avoirs_fournisseur',
    'commandes' // Just in case
];

foreach ($legacyTables as $table) {
    try {
        $conn->statement("DROP TABLE IF EXISTS `$table` CASCADE");
        echo "- Dropped legacy table: $table\n";
    } catch (\Exception $e) {
        echo "- Error dropping $table: " . $e->getMessage() . "\n";
    }
}

echo "Ensuring 'avoirs_achats' tables exist...\n";
try {
    $conn->statement("
        CREATE TABLE IF NOT EXISTS `avoirs_achats` (
            `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
            `numero`            VARCHAR(150) UNIQUE NOT NULL,
            `fournisseur_id`    BIGINT UNSIGNED NOT NULL,
            `facture_achat_id`  BIGINT UNSIGNED NULL,
            `date_avoir`        DATE NOT NULL,
            `motif`             TEXT NULL,
            `montant_ht`        DECIMAL(24,2) DEFAULT 0.00,
            `montant_tva`       DECIMAL(24,2) DEFAULT 0.00,
            `montant_ttc`       DECIMAL(24,2) DEFAULT 0.00,
            `statut`            ENUM('brouillon', 'valide', 'utilise', 'annule') DEFAULT 'brouillon',
            `devise_id`         BIGINT UNSIGNED NULL,
            `taux_change_document` DECIMAL(24,6) DEFAULT 1.000000,
            `created_by`        BIGINT UNSIGNED NULL,
            `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $conn->statement("
        CREATE TABLE IF NOT EXISTS `avoir_achat_lignes` (
            `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
            `avoir_achat_id`    BIGINT UNSIGNED NOT NULL,
            `produit_id`        BIGINT UNSIGNED NULL,
            `designation`       TEXT NOT NULL,
            `quantite`          DECIMAL(24,2) NOT NULL DEFAULT 1.00,
            `prix_unitaire`     DECIMAL(24,4) NOT NULL DEFAULT 0.00,
            `taux_tva`          DECIMAL(5,2) NOT NULL DEFAULT 20.00,
            `montant_ht`        DECIMAL(24,2) NOT NULL DEFAULT 0.00,
            `montant_tva`       DECIMAL(24,2) NOT NULL DEFAULT 0.00,
            `montant_ttc`       DECIMAL(24,2) NOT NULL DEFAULT 0.00,
            `ordre`             SMALLINT DEFAULT 0,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "- Avoirs tables created/verified.\n";
} catch (\Exception $e) {
    echo "- Error creating Avoirs tables: " . $e->getMessage() . "\n";
}

echo "SUCCESS!\n";
