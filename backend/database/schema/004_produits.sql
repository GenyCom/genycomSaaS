-- ============================================================
-- GenyCom Web SaaS — Produits & Familles
-- ============================================================

CREATE TABLE IF NOT EXISTS `famille_produit` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`     BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `code`          VARCHAR(50) NOT NULL,
    `libelle`       VARCHAR(150) NOT NULL,
    `detail`        VARCHAR(255) NULL,
    `parent_id`     BIGINT UNSIGNED NULL COMMENT 'Sous-famille',
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NULL,
    `deleted_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_code` (`code`),
    FOREIGN KEY (`parent_id`) REFERENCES `famille_produit`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `produits` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `famille_id`        BIGINT UNSIGNED NULL,
    `fournisseur_id`    BIGINT UNSIGNED NULL,
    `reference`         VARCHAR(50) NOT NULL,
    `reference_fournisseur` VARCHAR(50) NULL,
    `code_barre`        VARCHAR(50) NULL,
    `marque`            VARCHAR(100) NULL,
    `designation` TEXT NOT NULL,
    `detail`            LONGTEXT NULL,
    `unite`             VARCHAR(50) NULL,
    `image_path`        VARCHAR(500) NULL,
    `is_service`        TINYINT(1) DEFAULT 0,
    `is_actif`          TINYINT(1) DEFAULT 1,
    -- Prix
    `prix_ht_achat`     DECIMAL(24,4) DEFAULT 0.0000,
    `taux_tva`          DECIMAL(5,3) DEFAULT 0.000,
    `prix_ttc_achat`    DECIMAL(24,2) DEFAULT 0.00,
    `prix_revient`      DECIMAL(24,2) DEFAULT 0.00,
    `desc_revient`      LONGTEXT NULL,
    `prix_ht_vente`     DECIMAL(24,4) DEFAULT 0.0000,
    `marge_pourcentage` DECIMAL(5,2) DEFAULT 0.00,
    `prix_ttc_vente`    DECIMAL(24,2) DEFAULT 0.00,
    -- Logistique & Garantie
    `is_perissable`     TINYINT(1) DEFAULT 0,
    `is_lot`            TINYINT(1) DEFAULT 0,
    `garantie_mois`     INT DEFAULT 0,
    -- Stock
    `stock_actuel`      DECIMAL(24,2) DEFAULT 0.00,
    `stock_initial`     DECIMAL(24,2) DEFAULT 0.00,
    `seuil_alerte`      INT DEFAULT 0,
    `stock_min`         INT DEFAULT 0,
    `stock_max`         INT NULL,
    `emplacement_stock` VARCHAR(100) NULL,
    `delai_appro`       INT DEFAULT 0,
    -- Dimensions
    `poids`             DECIMAL(10,3) NULL,
    `dimensions`        VARCHAR(100) NULL,
    -- Audit
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ref_tenant` (`tenant_id`, `reference`),
    KEY `idx_designation` (`designation`),
    KEY `idx_code_barre` (`code_barre`),
    FOREIGN KEY (`famille_id`) REFERENCES `famille_produit`(`id`),
    FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `produit_images` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `produit_id`    BIGINT UNSIGNED NOT NULL,
    `image_path`    VARCHAR(500) NOT NULL,
    `is_principale` TINYINT(1) DEFAULT 0,
    `ordre`         INT DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tarifs_produit` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `produit_id`        BIGINT UNSIGNED NOT NULL,
    `type_client_id`    BIGINT UNSIGNED NULL,
    `quantite_min`      INT DEFAULT 1,
    `prix_ht`           DECIMAL(24,4) NOT NULL,
    `date_debut`        DATE NULL,
    `date_fin`          DATE NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`type_client_id`) REFERENCES `type_client`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `produit_fini` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `famille_id`        BIGINT UNSIGNED NULL,
    `reference`         VARCHAR(50) NOT NULL,
    `designation` TEXT NOT NULL,
    `detail`            LONGTEXT NULL,
    `image_path`        VARCHAR(500) NULL,
    `taux_tva`          DECIMAL(5,3) DEFAULT 0.000,
    `prix_tva`          DECIMAL(24,2) DEFAULT 0.00,
    `prix_ht`           DECIMAL(24,2) DEFAULT 0.00,
    `prix_ttc`          DECIMAL(24,2) DEFAULT 0.00,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`famille_id`) REFERENCES `famille_produit`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nomenclature_produit` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `produit_fini_id`   BIGINT UNSIGNED NOT NULL,
    `produit_id`        BIGINT UNSIGNED NOT NULL,
    `quantite`          DECIMAL(24,2) DEFAULT 1.00,
    `montant_ht`        DECIMAL(24,2) DEFAULT 0.00,
    `montant_tva`       DECIMAL(24,2) DEFAULT 0.00,
    `montant_ttc`       DECIMAL(24,2) DEFAULT 0.00,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`produit_fini_id`) REFERENCES `produit_fini`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `historique_produit` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`     BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `produit_id`    BIGINT UNSIGNED NOT NULL,
    `action`        VARCHAR(50) NOT NULL,
    `donnees_avant` JSON NULL,
    `donnees_apres` JSON NULL,
    `user_id`       BIGINT UNSIGNED NULL,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
