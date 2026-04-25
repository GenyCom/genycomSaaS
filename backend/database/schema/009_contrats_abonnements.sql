-- ============================================================
-- GenyCom Web SaaS — Facturation Récurrente (Contrats)
-- ============================================================

CREATE TABLE IF NOT EXISTS `contrats` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `titre`             VARCHAR(255) NOT NULL,
    `client_id`         BIGINT UNSIGNED NOT NULL,
    `date_debut`        DATE NOT NULL,
    `date_fin`          DATE NULL,
    `montant_ht`        DECIMAL(24,2) DEFAULT 0.00,
    `frequence_facturation` ENUM('mensuel','trimestriel','annuel') DEFAULT 'mensuel',
    `statut`            ENUM('brouillon','actif','suspendu','termine','annule') DEFAULT 'brouillon',
    `devise_id`         BIGINT UNSIGNED NULL,
    `taux_change_document` DECIMAL(24,6) DEFAULT 1.000000,
    `prochaine_facture` DATE NULL,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    FOREIGN KEY (`devise_id`) REFERENCES `devise`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ligne_contrat` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `contrat_id`        BIGINT UNSIGNED NOT NULL,
    `produit_id`        BIGINT UNSIGNED NULL,
    `designation`       VARCHAR(255) NOT NULL,
    `quantite`          DECIMAL(24,2) DEFAULT 1.00,
    `prix_unitaire_ht`  DECIMAL(24,2) DEFAULT 0.00,
    `taux_tva`          DECIMAL(5,3) DEFAULT 0.000,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`contrat_id`) REFERENCES `contrats`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
