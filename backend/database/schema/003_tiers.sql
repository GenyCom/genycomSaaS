-- ============================================================
-- GenyCom Web SaaS — Tiers (Clients & Fournisseurs)
-- ============================================================

CREATE TABLE IF NOT EXISTS `type_client` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle`       VARCHAR(100) NOT NULL,
    `detail`        VARCHAR(255) NULL,
    `exempt_tva`    TINYINT(1) DEFAULT 0,
    `vip`           TINYINT(1) DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NULL,
    `deleted_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `type_fournisseur` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle`       VARCHAR(100) NOT NULL,
    `detail`        VARCHAR(255) NULL,
    `vip`           TINYINT(1) DEFAULT 0,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NULL,
    `deleted_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clients` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code_client`       VARCHAR(50) NULL,
    `societe`           VARCHAR(255) NOT NULL,
    `is_personne_physique` TINYINT(1) DEFAULT 0,
    `civilite`          VARCHAR(10) NULL,
    `nom`               VARCHAR(100) NULL,
    `prenom`            VARCHAR(100) NULL,
    `ice`               VARCHAR(100) NULL,
    `rc`                VARCHAR(50) NULL,
    `adresse`           VARCHAR(500) NULL,
    `ville`             VARCHAR(100) NULL,
    `code_postal`       VARCHAR(20) NULL,
    `pays`              VARCHAR(100) DEFAULT 'Maroc',
    `telephone`         VARCHAR(20) NULL,
    `mobile`            VARCHAR(20) NULL,
    `fax`               VARCHAR(20) NULL,
    `email`             VARCHAR(255) NULL,
    `site_web`          VARCHAR(255) NULL,
    `rib`               VARCHAR(50) NULL,
    `banque`            VARCHAR(100) NULL,
    `image_path`        VARCHAR(500) NULL,
    `observations`      LONGTEXT NULL,
    `exempt_tva`        TINYINT(1) DEFAULT 0,
    `type_client_id`    BIGINT UNSIGNED NULL,
    `plafond_credit`    DECIMAL(24,2) DEFAULT 0.00,
    `delai_paiement`    INT DEFAULT 0,
    `montant_rest_du`   DECIMAL(24,2) DEFAULT 0.00,
    `commercial_id`     BIGINT UNSIGNED NULL,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code_client` (`code_client`),
    KEY `idx_societe` (`societe`),
    FOREIGN KEY (`type_client_id`) REFERENCES `type_client`(`id`),
    FOREIGN KEY (`commercial_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fournisseurs` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code_fournisseur`  VARCHAR(50) NULL,
    `societe`           VARCHAR(255) NOT NULL,
    `is_personne_physique` TINYINT(1) DEFAULT 0,
    `civilite`          VARCHAR(10) NULL,
    `nom`               VARCHAR(100) NULL,
    `prenom`            VARCHAR(100) NULL,
    `ice`               VARCHAR(30) NULL,
    `rc`                VARCHAR(50) NULL,
    `adresse`           VARCHAR(500) NULL,
    `ville`             VARCHAR(100) NULL,
    `code_postal`       VARCHAR(20) NULL,
    `pays`              VARCHAR(100) DEFAULT 'Maroc',
    `telephone`         VARCHAR(20) NULL,
    `mobile`            VARCHAR(20) NULL,
    `fax`               VARCHAR(20) NULL,
    `email`             VARCHAR(255) NULL,
    `site_web`          VARCHAR(255) NULL,
    `rib`               VARCHAR(50) NULL,
    `banque`            VARCHAR(100) NULL,
    `image_path`        VARCHAR(500) NULL,
    `observations`      LONGTEXT NULL,
    `type_fournisseur_id` BIGINT UNSIGNED NULL,
    `delai_livraison`   INT DEFAULT 0,
    `created_by`        BIGINT UNSIGNED NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_code_fourn` (`code_fournisseur`),
    KEY `idx_societe` (`societe`),
    FOREIGN KEY (`type_fournisseur_id`) REFERENCES `type_fournisseur`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contacts` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contactable_type`  VARCHAR(100) NOT NULL,
    `contactable_id`    BIGINT UNSIGNED NOT NULL,
    `nom`               VARCHAR(100) NOT NULL,
    `prenom`            VARCHAR(100) NULL,
    `fonction`          VARCHAR(100) NULL,
    `email`             VARCHAR(255) NULL,
    `telephone`         VARCHAR(20) NULL,
    `mobile`            VARCHAR(20) NULL,
    `is_principal`      TINYINT(1) DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_contactable` (`contactable_type`, `contactable_id`),
 REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `adresses_livraison` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`         BIGINT UNSIGNED NOT NULL,
    `adresse`           VARCHAR(500) NOT NULL,
    `ville`             VARCHAR(100) NULL,
    `code_postal`       VARCHAR(20) NULL,
    `pays`              VARCHAR(100) NULL,
    `contact`           VARCHAR(100) NULL,
    `telephone`         VARCHAR(20) NULL,
    `observations`      TEXT NULL,
    `is_default`        TINYINT(1) DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `adresses_facturation` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`         BIGINT UNSIGNED NOT NULL,
    `adresse`           VARCHAR(500) NOT NULL,
    `ville`             VARCHAR(100) NULL,
    `code_postal`       VARCHAR(20) NULL,
    `pays`              VARCHAR(100) NULL,
    `contact`           VARCHAR(100) NULL,
    `telephone`         VARCHAR(20) NULL,
    `observations`      TEXT NULL,
    `is_default`        TINYINT(1) DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

