-- ============================================================
-- GenyCom Web SaaS — Workflow Ventes (BC Client & Linking)
-- ============================================================

-- Table des Bons de Commande Client (BCC)
CREATE TABLE IF NOT EXISTS `bons_commande_client` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL,
    `numero`            VARCHAR(150) NULL,
    `date_commande`     DATE NOT NULL,
    `date_livraison_prevue` DATE NULL,
    `client_id`         BIGINT UNSIGNED NOT NULL,
    `projet_id`         BIGINT UNSIGNED NULL,
    `devis_id`          BIGINT UNSIGNED NULL COMMENT 'Source Devis',
    `total_ht`          DECIMAL(24,2) DEFAULT 0.00,
    `total_tva`         DECIMAL(24,2) DEFAULT 0.00,
    `total_ttc`         DECIMAL(24,2) DEFAULT 0.00,
    `total_remise`      DECIMAL(24,2) DEFAULT 0.00,
    `etat_id`           BIGINT UNSIGNED NULL,
    `observations`      LONGTEXT NULL,
    `conditions`        LONGTEXT NULL,
    `est_livre`         TINYINT(1) DEFAULT 0,
    `est_facture`       TINYINT(1) DEFAULT 0,
    `devise_id`         BIGINT UNSIGNED NULL,
    `taux_change_document` DECIMAL(24,6) DEFAULT 1.000000,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_numero_bcc` (`numero`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    FOREIGN KEY (`projet_id`) REFERENCES `projets`(`id`),
    FOREIGN KEY (`devis_id`) REFERENCES `devis`(`id`),
    FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`),
    FOREIGN KEY (`devise_id`) REFERENCES `devise`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lignes du Bon de Commande Client
CREATE TABLE IF NOT EXISTS `ligne_bon_commande_client` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`             BIGINT UNSIGNED NOT NULL,
    `bon_commande_client_id` BIGINT UNSIGNED NOT NULL,
    `produit_id`            BIGINT UNSIGNED NULL,
    `designation` TEXT NOT NULL,
    `description`           TEXT NULL,
    `quantite`              DECIMAL(24,2) DEFAULT 1.00,
    `unite`                 VARCHAR(50) NULL,
    `prix_unitaire`         DECIMAL(24,4) DEFAULT 0.0000,
    `taux_tva`              DECIMAL(5,3) DEFAULT 0.000,
    `remise_pourcent`       DECIMAL(5,2) DEFAULT 0.00,
    `remise_montant`        DECIMAL(24,2) DEFAULT 0.00,
    `montant_ht`            DECIMAL(24,2) DEFAULT 0.00,
    `montant_tva`           DECIMAL(24,2) DEFAULT 0.00,
    `montant_ttc`           DECIMAL(24,2) DEFAULT 0.00,
    `ordre`                 SMALLINT DEFAULT 0,
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`bon_commande_client_id`) REFERENCES `bons_commande_client`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mise à jour de Bon de Livraison pour lier au BC
-- Note: Si la table bons_livraison existe déjà (via 005_ventes.sql)
ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `bon_commande_client_id` BIGINT UNSIGNED NULL AFTER `devis_id`;
ALTER TABLE `bons_livraison` ADD CONSTRAINT `fk_bl_bcc` FOREIGN KEY (`bon_commande_client_id`) REFERENCES `bons_commande_client`(`id`);

ALTER TABLE `factures` ADD COLUMN IF NOT EXISTS `bon_commande_client_id` BIGINT UNSIGNED NULL AFTER `devis_id`;
ALTER TABLE `factures` ADD CONSTRAINT `fk_facture_bcc` FOREIGN KEY (`bon_commande_client_id`) REFERENCES `bons_commande_client`(`id`);

-- Ajout d'états de base pour BCC si nécessaire
INSERT IGNORE INTO `etat_document` (`type_document`, `code`, `libelle`, `ordre`, `couleur`, `is_system`)
VALUES 
('bcc', 'BROUILLON', 'Brouillon', 10, '#94a3b8', 1),
('bcc', 'VALIDE', 'Validé', 20, '#3b82f6', 1),
('bcc', 'LIVRE', 'Livré', 30, '#10b981', 1),
('bcc', 'FACTURE', 'Facturé', 40, '#6366f1', 1),
('bcc', 'ANNULE', 'Annulé', 99, '#ef4444', 1);
