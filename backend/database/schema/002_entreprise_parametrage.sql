-- ============================================================
-- GenyCom Web SaaS — Entreprise & Paramétrage
-- ============================================================

CREATE TABLE IF NOT EXISTS `entreprise` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `raison_sociale`        VARCHAR(255) NOT NULL,
    `forme_juridique`       VARCHAR(100) NULL,
    `ice`                   VARCHAR(30) NULL,
    `rc`                    VARCHAR(50) NULL,
    `patente`               VARCHAR(50) NULL,
    `if_fiscal`             VARCHAR(50) NULL,
    `cnss`                  VARCHAR(50) NULL,
    `adresse`               VARCHAR(500) NOT NULL,
    `ville`                 VARCHAR(100) NOT NULL,
    `code_postal`           VARCHAR(20) NULL,
    `pays`                  VARCHAR(100) DEFAULT 'Maroc',
    `telephone`             VARCHAR(20) NULL,
    `fax`                   VARCHAR(20) NULL,
    `email`                 VARCHAR(255) NULL,
    `site_web`              VARCHAR(255) NULL,
    `logo_path`             VARCHAR(500) NULL,
    `rib`                   VARCHAR(50) NULL,
    `banque`                VARCHAR(100) NULL,
    `devise_id`             BIGINT UNSIGNED NULL,
    `exercice_debut`        TINYINT DEFAULT 1,
    `format_numero_facture` VARCHAR(100) DEFAULT 'FAC-{YYYY}{MM}-{SEQ}',
    `format_numero_devis`   VARCHAR(100) DEFAULT 'DV-{YYYY}{MM}{DD}-{SEQ}',
    `format_numero_cmd`     VARCHAR(100) DEFAULT 'CMD-{YYYY}{MM}{DD}-{SEQ}',
    `format_numero_bl`      VARCHAR(100) DEFAULT 'BL-{YYYY}{MM}{DD}-{SEQ}',
    `format_numero_br`      VARCHAR(100) DEFAULT 'BR-{YYYY}{MM}{DD}-{SEQ}',
    `format_numero_avoir`   VARCHAR(100) DEFAULT 'AV-{YYYY}{MM}-{SEQ}',
    `entete_impression`     LONGTEXT NULL,
    `pied_page_impression`  LONGTEXT NULL,
    `conditions_generales`  LONGTEXT NULL,
    `created_at`            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `devise` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom`               VARCHAR(100) NOT NULL,
    `code_iso`          VARCHAR(3) NULL,
    `symbole`           VARCHAR(10) NULL,
    `taux_change`       DECIMAL(12,6) DEFAULT 1.000000,
    `is_principale`     TINYINT(1) DEFAULT 0,
    `actif`             TINYINT(1) DEFAULT 1,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `taux_tva` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `taux`              DECIMAL(5,3) NOT NULL DEFAULT 0.000,
    `libelle`           VARCHAR(100) NULL,
    `detail`            VARCHAR(255) NULL,
    `actif`             TINYINT(1) DEFAULT 1,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_taux_tenant` (`taux`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mode_reglement` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle`           VARCHAR(100) NOT NULL,
    `detail`            VARCHAR(255) NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mode_livraison` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle`           VARCHAR(100) NOT NULL,
    `detail`            VARCHAR(255) NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `condition_reglement` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle`           VARCHAR(100) NOT NULL,
    `detail`            VARCHAR(255) NULL,
    `nombre_jours`      INT DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sequences_numerotation` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_document`     VARCHAR(50) NOT NULL,
    `prefixe`           VARCHAR(50) NOT NULL,
    `annee`             SMALLINT NOT NULL,
    `mois`              TINYINT NULL,
    `dernier_numero`    INT NOT NULL DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_seq` (`type_document`, `prefixe`, `annee`, `mois`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `etat_document` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_document`     VARCHAR(50) NOT NULL COMMENT 'facture, commande, devis, dette, depense',
    `code`              VARCHAR(10) NOT NULL,
    `libelle`           VARCHAR(100) NOT NULL,
    `ordre`             INT DEFAULT 0,
    `couleur`           VARCHAR(20) DEFAULT '#6b7280',
    `detail`            VARCHAR(255) NULL,
    `is_system`         TINYINT(1) DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_etat` (`type_document`, `code`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

