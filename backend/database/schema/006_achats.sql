-- ============================================================
-- GenyCom Web SaaS — Cycle d'Achat (Commandes, BR, Dettes, Avoirs Fournisseur)
-- ============================================================

-- Commandes Fournisseur
CREATE TABLE IF NOT EXISTS `commandes` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `numero`            VARCHAR(150) NULL,
    `date_commande`     DATE NOT NULL,
    `date_livraison_prevue` DATE NULL,
    `fournisseur_id`    BIGINT UNSIGNED NOT NULL,
    `total_ht`          DECIMAL(24,2) DEFAULT 0.00,
    `total_tva`         DECIMAL(24,2) DEFAULT 0.00,
    `total_ttc`         DECIMAL(24,2) DEFAULT 0.00,
    `etat_id`           BIGINT UNSIGNED NULL,
    `est_livree`        TINYINT(1) DEFAULT 0,
    `observations`      LONGTEXT NULL,
    `devise_id`         BIGINT UNSIGNED NULL,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_numero` (`numero`),
    KEY `idx_fournisseur` (`fournisseur_id`),
    FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs`(`id`),
    FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`),
    FOREIGN KEY (`devise_id`) REFERENCES `devise`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ligne_commande` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `commande_id`       BIGINT UNSIGNED NOT NULL,
    `produit_id`        BIGINT UNSIGNED NOT NULL,
    `designation`       VARCHAR(255) NOT NULL,
    `quantite`          DECIMAL(24,2) DEFAULT 1.00,
    `prix_unitaire`     DECIMAL(24,4) DEFAULT 0.0000,
    `taux_tva`          DECIMAL(5,3) DEFAULT 0.000,
    `remise_montant`    DECIMAL(24,2) DEFAULT 0.00,
    `montant_ht`        DECIMAL(24,2) DEFAULT 0.00,
    `montant_tva`       DECIMAL(24,2) DEFAULT 0.00,
    `montant_ttc`       DECIMAL(24,2) DEFAULT 0.00,
    `ordre`             SMALLINT DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`commande_id`) REFERENCES `commandes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bons de réception
CREATE TABLE IF NOT EXISTS `bons_reception` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `numero`            VARCHAR(150) NULL,
    `date_reception`    DATE NOT NULL,
    `commande_id`       BIGINT UNSIGNED NOT NULL,
    `fournisseur_id`    BIGINT UNSIGNED NOT NULL,
    `observations`      LONGTEXT NULL,
    `statut`            ENUM('brouillon','valide','annule') DEFAULT 'brouillon',
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`commande_id`) REFERENCES `commandes`(`id`),
    FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ligne_bon_reception` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `bon_reception_id`  BIGINT UNSIGNED NOT NULL,
    `produit_id`        BIGINT UNSIGNED NOT NULL,
    `designation`       VARCHAR(255) NOT NULL,
    `quantite_commandee` DECIMAL(24,2) DEFAULT 0.00,
    `quantite_recue`    DECIMAL(24,2) DEFAULT 0.00,
    `prix_unitaire`     DECIMAL(24,4) DEFAULT 0.0000,
    `lot_numero`        VARCHAR(100) NULL,
    `date_peremption`   DATE NULL,
    `ordre`             SMALLINT DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`bon_reception_id`) REFERENCES `bons_reception`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dettes Fournisseur
CREATE TABLE IF NOT EXISTS `dettes_fournisseur` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `numero`                VARCHAR(150) NULL,
    `fournisseur_id`        BIGINT UNSIGNED NOT NULL,
    `commande_id`           BIGINT UNSIGNED NULL,
    `bon_reception_id`      BIGINT UNSIGNED NULL,
    `date_dette`            DATE NOT NULL,
    `date_echeance`         DATE NULL,
    `montant_total`         DECIMAL(24,2) DEFAULT 0.00,
    `montant_regle`         DECIMAL(24,2) DEFAULT 0.00,
    `montant_restant`       DECIMAL(24,2) DEFAULT 0.00,
    `etat_id`               BIGINT UNSIGNED NULL,
    `mode_reglement_id`     BIGINT UNSIGNED NULL,
    `observations`          LONGTEXT NULL,
    `created_by`            BIGINT UNSIGNED NULL,
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NULL,
    `deleted_at`            TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs`(`id`),
    FOREIGN KEY (`commande_id`) REFERENCES `commandes`(`id`),
    FOREIGN KEY (`bon_reception_id`) REFERENCES `bons_reception`(`id`),
    FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`),
    FOREIGN KEY (`mode_reglement_id`) REFERENCES `mode_reglement`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Avoirs Fournisseur
CREATE TABLE IF NOT EXISTS `avoirs_fournisseur` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `numero`                VARCHAR(150) NULL,
    `fournisseur_id`        BIGINT UNSIGNED NOT NULL,
    `dette_id`              BIGINT UNSIGNED NULL,
    `date_avoir`            DATE NOT NULL,
    `motif_retour`          VARCHAR(500) NULL,
    `total_ht`              DECIMAL(24,2) DEFAULT 0.00,
    `total_tva`             DECIMAL(24,2) DEFAULT 0.00,
    `total_ttc`             DECIMAL(24,2) DEFAULT 0.00,
    `montant_regle`         DECIMAL(24,2) DEFAULT 0.00,
    `etat_id`               BIGINT UNSIGNED NULL,
    `observations`          LONGTEXT NULL,
    `created_by`            BIGINT UNSIGNED NULL,
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NULL,
    `deleted_at`            TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs`(`id`),
    FOREIGN KEY (`dette_id`) REFERENCES `dettes_fournisseur`(`id`),
    FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ligne_avoir_fournisseur` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `avoir_id`          BIGINT UNSIGNED NOT NULL,
    `produit_id`        BIGINT UNSIGNED NULL,
    `designation`       VARCHAR(255) NOT NULL,
    `quantite`          DECIMAL(24,2) DEFAULT 1.00,
    `prix_unitaire`     DECIMAL(24,4) DEFAULT 0.0000,
    `taux_tva`          DECIMAL(5,3) DEFAULT 0.000,
    `montant_ht`        DECIMAL(24,2) DEFAULT 0.00,
    `montant_tva`       DECIMAL(24,2) DEFAULT 0.00,
    `montant_ttc`       DECIMAL(24,2) DEFAULT 0.00,
    `ordre`             SMALLINT DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`avoir_id`) REFERENCES `avoirs_fournisseur`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

