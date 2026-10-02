-- ============================================================
-- GenyCom Web SaaS — Clôtures de Caisse (Rapport Z)
-- ============================================================

CREATE TABLE IF NOT EXISTS `pos_clotures` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`         BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `numero`            VARCHAR(50) NOT NULL,
    `date_cloture`      DATE NOT NULL,
    `opened_at`         DATETIME NOT NULL,
    `closed_at`         DATETIME NOT NULL,
    `user_id`           BIGINT UNSIGNED NULL,
    `nom_caissier`      VARCHAR(150) NULL,
    `fond_initial`      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_ventes`      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_especes`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_carte`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_cheque`      DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_virement`    DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_autre`       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_attendu`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_declare`     DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `ecart`             DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `statut_ecart`      VARCHAR(30) NOT NULL DEFAULT 'conforme',
    `nb_ventes`         INT UNSIGNED NOT NULL DEFAULT 0,
    `observations`      TEXT NULL,
    `rapport_texte`     MEDIUMTEXT NULL,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP NULL,
    `deleted_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tenant_date` (`tenant_id`, `date_cloture`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
