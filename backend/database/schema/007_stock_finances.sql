-- ============================================================
-- GenyCom Web SaaS — Stock, Inventaire, Finances, Transversal
-- ============================================================

-- Entrepôts
CREATE TABLE IF NOT EXISTS `entrepots` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`     BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `code`          VARCHAR(20) NOT NULL,
    `nom`           VARCHAR(100) NOT NULL,
    `adresse`       VARCHAR(500) NULL,
    `responsable_id` BIGINT UNSIGNED NULL,
    `is_default`    TINYINT(1) DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NULL,
    `deleted_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code_entrepot` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock
CREATE TABLE IF NOT EXISTS `stocks` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `produit_id`        BIGINT UNSIGNED NOT NULL,
    `entrepot_id`       BIGINT UNSIGNED NULL,
    `quantite`          DECIMAL(24,2) DEFAULT 0.00,
    `lot_numero`        VARCHAR(100) NULL,
    `date_peremption`   DATE NULL,
    `notes`             LONGTEXT NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_produit` (`produit_id`),
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`),
    FOREIGN KEY (`entrepot_id`) REFERENCES `entrepots`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mouvements de stock
CREATE TABLE IF NOT EXISTS `mouvements_stock` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`             BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `stock_id`              BIGINT UNSIGNED NOT NULL,
    `produit_id`            BIGINT UNSIGNED NOT NULL,
    `type_mouvement`        ENUM('entree_achat','sortie_vente','entree_retour','sortie_retour',
                                 'ajustement_positif','ajustement_negatif','transfert_in',
                                 'transfert_out','inventaire') NOT NULL,
    `quantite`              DECIMAL(24,2) NOT NULL DEFAULT 0.00,
    `libelle`               VARCHAR(255) NULL,
    `reference_document`    VARCHAR(150) NULL,
    `document_type`         VARCHAR(100) NULL,
    `document_id`           BIGINT UNSIGNED NULL,
    `entrepot_source_id`    BIGINT UNSIGNED NULL,
    `entrepot_dest_id`      BIGINT UNSIGNED NULL,
    `prix_unitaire`         DECIMAL(24,4) DEFAULT 0.0000,
    `created_by`            BIGINT UNSIGNED NULL,
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_stock` (`stock_id`),
    KEY `idx_produit` (`produit_id`),
    KEY `idx_date` (`created_at`),
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`),
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventaires
CREATE TABLE IF NOT EXISTS `inventaires` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL,
    `code`              VARCHAR(50) NOT NULL,
    `date_inventaire`   DATE NOT NULL,
    `entrepot_id`       BIGINT UNSIGNED NULL,
    `statut`            ENUM('brouillon','en_cours','valide','annule') DEFAULT 'brouillon',
    `observations`      LONGTEXT NULL,
    `valide_par`        BIGINT UNSIGNED NULL,
    `date_validation`   DATETIME NULL,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code_inv` (`code`),
    FOREIGN KEY (`entrepot_id`) REFERENCES `entrepots`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventaire_lignes` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `inventaire_id`     BIGINT UNSIGNED NOT NULL,
    `produit_id`        BIGINT UNSIGNED NOT NULL,
    `stock_theorique`   DECIMAL(24,2) DEFAULT 0.00,
    `stock_physique`    DECIMAL(24,2) DEFAULT 0.00,
    `ecart`             DECIMAL(24,2) DEFAULT 0.00,
    `observations`      VARCHAR(500) NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`inventaire_id`) REFERENCES `inventaires`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alertes stock
CREATE TABLE IF NOT EXISTS `alertes_stock` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `produit_id`    BIGINT UNSIGNED NOT NULL,
    `type_alerte`   ENUM('seuil_min','seuil_max','peremption','rupture') NOT NULL,
    `message`       VARCHAR(500) NOT NULL,
    `est_lue`       TINYINT(1) DEFAULT 0,
    `est_traitee`   TINYINT(1) DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ FINANCES ============

-- Règlements unifiés
CREATE TABLE IF NOT EXISTS `reglements` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `payable_type`      VARCHAR(100) NOT NULL,
    `payable_id`        BIGINT UNSIGNED NOT NULL,
    `date_reglement`    DATE NOT NULL,
    `montant`           DECIMAL(24,2) NOT NULL,
    `mode_reglement_id` BIGINT UNSIGNED NULL,
    `numero_cheque`     VARCHAR(100) NULL,
    `banque`            VARCHAR(100) NULL,
    `reference_virement` VARCHAR(100) NULL,
    `observations`      LONGTEXT NULL,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_payable` (`payable_type`, `payable_id`),
    FOREIGN KEY (`mode_reglement_id`) REFERENCES `mode_reglement`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Échéancier
CREATE TABLE IF NOT EXISTS `echeancier` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `echeancable_type`  VARCHAR(100) NOT NULL,
    `echeancable_id`    BIGINT UNSIGNED NOT NULL,
    `numero_echeance`   SMALLINT DEFAULT 1,
    `date_echeance`     DATE NOT NULL,
    `montant`           DECIMAL(24,2) NOT NULL,
    `montant_regle`     DECIMAL(24,2) DEFAULT 0.00,
    `statut`            ENUM('a_venir','en_cours','payee','en_retard') DEFAULT 'a_venir',
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_echeancable` (`echeancable_type`, `echeancable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dépenses (Frais de fonctionnement)
CREATE TABLE IF NOT EXISTS `depenses` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `code`              VARCHAR(50) NULL,
    `libelle`           VARCHAR(255) NOT NULL,
    `date_depense`      DATE NOT NULL,
    `montant`           DECIMAL(24,2) DEFAULT 0.00,
    `categorie_id`      BIGINT UNSIGNED NULL,
    `etat_id`           BIGINT UNSIGNED NULL,
    `note_reglement`    LONGTEXT NULL,
    `is_recurrente`     TINYINT(1) DEFAULT 0,
    `frequence`         ENUM('mensuel','trimestriel','annuel') NULL,
    `devise_id`         BIGINT UNSIGNED NULL,
    `taux_change_document` DECIMAL(24,6) DEFAULT 1.000000,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`categorie_id`) REFERENCES `categorie_depense`(`id`),
    FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`),
    FOREIGN KEY (`devise_id`) REFERENCES `devise`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ TRANSVERSAL ============

-- Fichiers centralisés
CREATE TABLE IF NOT EXISTS `fichiers` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fileable_type`     VARCHAR(100) NOT NULL,
    `fileable_id`       BIGINT UNSIGNED NOT NULL,
    `nom_original`      VARCHAR(255) NOT NULL,
    `nom_stocke`        VARCHAR(255) NOT NULL,
    `chemin`            VARCHAR(500) NOT NULL,
    `mime_type`         VARCHAR(100) NULL,
    `taille`            BIGINT UNSIGNED DEFAULT 0,
    `categorie`         VARCHAR(50) NULL,
    `uploaded_by`       BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_fileable` (`fileable_type`, `fileable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal d'activité
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           BIGINT UNSIGNED NULL,
    `action`            VARCHAR(50) NOT NULL,
    `subject_type`      VARCHAR(100) NOT NULL,
    `subject_id`        BIGINT UNSIGNED NULL,
    `description`       VARCHAR(500) NULL,
    `properties`        JSON NULL,
    `ip_address`        VARCHAR(45) NULL,
    `user_agent`        VARCHAR(500) NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_subject` (`subject_type`, `subject_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`                CHAR(36) NOT NULL,
    `type`              VARCHAR(255) NOT NULL,
    `notifiable_type`   VARCHAR(255) NOT NULL,
    `notifiable_id`     BIGINT UNSIGNED NOT NULL,
    `data`              JSON NOT NULL,
    `read_at`           TIMESTAMP NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_notifiable` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Import logs
CREATE TABLE IF NOT EXISTS `import_logs` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           BIGINT UNSIGNED NOT NULL,
    `type_import`       VARCHAR(50) NOT NULL,
    `fichier_original`  VARCHAR(255) NOT NULL,
    `fichier_path`      VARCHAR(500) NOT NULL,
    `nombre_lignes`     INT DEFAULT 0,
    `nombre_succes`     INT DEFAULT 0,
    `nombre_erreurs`    INT DEFAULT 0,
    `erreurs_detail`    JSON NULL,
    `statut`            ENUM('en_cours','termine','erreur') DEFAULT 'en_cours',
    `document_id`       BIGINT UNSIGNED NULL,
    `document_type`     VARCHAR(100) NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

