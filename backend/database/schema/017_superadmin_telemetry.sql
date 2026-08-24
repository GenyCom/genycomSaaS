-- ==============================================================================
-- GENYCOM SAAS — SCRIPT SQL 017 : SUPERADMIN TELEMETRY & USER ACTIVITY TRACKING
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Ajout des colonnes de suivi d'activité dans la table centrale `users`
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `last_seen_at` TIMESTAMP NULL AFTER `last_login_ip`,
    ADD COLUMN IF NOT EXISTS `last_seen_ip` VARCHAR(45) NULL AFTER `last_seen_at`,
    ADD INDEX IF NOT EXISTS `idx_users_last_seen_at` (`last_seen_at`);

-- 2. Ajout du plan d'abonnement dans la table centrale `tenants`
ALTER TABLE `tenants`
    ADD COLUMN IF NOT EXISTS `plan` VARCHAR(50) DEFAULT 'Business' AFTER `statut`;

-- 3. Création de la table de télémétrie des erreurs serveur (`telemetry_error_logs`)
CREATE TABLE IF NOT EXISTS `telemetry_error_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `status_code` INT DEFAULT 500,
    `message` TEXT NOT NULL,
    `file` VARCHAR(255) NULL,
    `line` INT NULL,
    `url` VARCHAR(500) NULL,
    `method` VARCHAR(10) NULL,
    `ip` VARCHAR(45) NULL,
    `trace` LONGTEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_telemetry_tenant_id` (`tenant_id`),
    KEY `idx_telemetry_user_id` (`user_id`),
    KEY `idx_telemetry_status_code` (`status_code`),
    KEY `idx_telemetry_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
