-- ============================================================
-- GenyCom Web SaaS — Paramétrage Entreprise & Séquences
-- ============================================================

CREATE TABLE IF NOT EXISTS `entreprise` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `raison_sociale`    VARCHAR(255) NOT NULL,
    `forme_juridique`   VARCHAR(100) NULL,
    `logo_path`         VARCHAR(255) NULL,
    `adresse`           TEXT NULL,
    `ville`             VARCHAR(100) NULL,
    `pays`              VARCHAR(100) DEFAULT 'Maroc',
    `ice`               VARCHAR(50) NULL,
    `rc`                VARCHAR(50) NULL,
    `if`                VARCHAR(50) NULL,
    `patente`           VARCHAR(50) NULL,
    `telephone`         VARCHAR(50) NULL,
    `email`             VARCHAR(255) NULL,
    `site_web`          VARCHAR(255) NULL,
    
    -- Formats de numérotation
    `format_numero_facture` VARCHAR(50) DEFAULT 'FAC-{YYYY}-{SEQ}',
    `format_numero_devis`   VARCHAR(50) DEFAULT 'DV-{YYYY}{MM}-{SEQ}',
    `format_numero_bcc`     VARCHAR(50) DEFAULT 'BCC-{YYYY}{MM}-{SEQ}',
    `format_numero_bl`      VARCHAR(50) DEFAULT 'BL-{YYYY}{MM}-{SEQ}',
    `format_numero_br`      VARCHAR(50) DEFAULT 'BR-{YYYY}{MM}-{SEQ}',
    `format_numero_cmd`     VARCHAR(50) DEFAULT 'CMD-{YYYY}{MM}-{SEQ}',
    `format_numero_avoir`   VARCHAR(50) DEFAULT 'AV-{YYYY}{MM}-{SEQ}',
    
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sequences_numerotation` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `type_document`     VARCHAR(50) NOT NULL,
    `prefixe`           VARCHAR(50) NOT NULL,
    `annee`             SMALLINT NOT NULL,
    `mois`              TINYINT NULL,
    `dernier_numero`    INT NOT NULL DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_seq` (`tenant_id`, `type_document`, `prefixe`, `annee`, `mois`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `etat_document` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_document`     VARCHAR(50) NOT NULL COMMENT 'facture, commande, devis, dette, depense',
    `code`              VARCHAR(10) NOT NULL,
    `libelle`           VARCHAR(50) NOT NULL,
    `ordre`             INT DEFAULT 0,
    `couleur`           VARCHAR(20) DEFAULT '#3b82f6',
    `detail`            TEXT NULL,
    `is_system`         TINYINT(1) DEFAULT 0,
	`is_default` 		TINYINT(1) NOT NULL DEFAULT 0,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_etat_code` (`tenant_id`, `type_document`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `devise` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `nom`               VARCHAR(50) NOT NULL,
    `symbole`           VARCHAR(10) NOT NULL,
    `taux_change`       DECIMAL(24,6) DEFAULT 1.000000,
    `is_principale`     TINYINT(1) DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `taux_tva` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `taux`              DECIMAL(5,2) NOT NULL,
    `libelle`           VARCHAR(50) NOT NULL,
    `detail`            VARCHAR(255) NULL,
    `actif`             TINYINT(1) DEFAULT 1,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
	`ordre` INT 		DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `condition_reglement` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `libelle`           VARCHAR(100) NOT NULL,
    `detail`            VARCHAR(255) NULL,
    `nombre_jours`      INT DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mode_reglement` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `libelle`           VARCHAR(100) NOT NULL,
    `detail`            VARCHAR(255) NULL,
	`ordre` 			INT DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mode_livraison` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `libelle`           VARCHAR(100) NOT NULL,
    `detail`            VARCHAR(255) NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categorie_depense` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `libelle`           VARCHAR(150) NOT NULL,
    `detail`            VARCHAR(255) NULL,
    `parent_id`         BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`parent_id`) REFERENCES `categorie_depense`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
