-- ============================================================
-- GenyCom Web SaaS — Facturation Récurrente (Contrats)
-- ============================================================

CREATE TABLE IF NOT EXISTS `contrats` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(50) DEFAULT NULL,
  `tenant_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 1,
  `titre` VARCHAR(255) NOT NULL,
  `client_id` BIGINT(20) UNSIGNED NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE DEFAULT NULL,
  `montant_ht` DECIMAL(24,2) DEFAULT 0.00,
  `total_ht` DECIMAL(24,2) DEFAULT 0.00,
  `total_tva` DECIMAL(24,2) DEFAULT 0.00,
  `total_ttc` DECIMAL(24,2) DEFAULT 0.00,
  `frequence_facturation` ENUM('mensuel','trimestriel','annuel') DEFAULT 'mensuel',
  `frequence` VARCHAR(50) DEFAULT NULL,
  `statut` ENUM('brouillon','actif','suspendu','termine','annule') DEFAULT 'brouillon',
  `observations` TEXT DEFAULT NULL,
  `devise_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `taux_change_document` DECIMAL(24,6) DEFAULT 1.000000,
  `prochaine_facture` DATE DEFAULT NULL,
  `prochaine_echeance` DATE DEFAULT NULL,
  `created_by` BIGINT(20) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `contrats_client_id_foreign` (`client_id`),
  KEY `contrats_devise_id_foreign` (`devise_id`),
  CONSTRAINT `contrats_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contrats_devise_id_foreign` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `ligne_contrat` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 1,
  `contrat_id` BIGINT(20) UNSIGNED NOT NULL,
  `produit_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `designation` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `quantite` DECIMAL(24,2) DEFAULT 1.00,
  `prix_unitaire_ht` DECIMAL(24,2) DEFAULT 0.00,
  `prix_unitaire` DECIMAL(24,2) DEFAULT 0.00,
  `montant_ht` DECIMAL(24,2) DEFAULT 0.00,
  `montant_tva` DECIMAL(24,2) DEFAULT 0.00,
  `montant_ttc` DECIMAL(24,2) DEFAULT 0.00,
  `ordre` INT(11) DEFAULT 0,
  `taux_tva` DECIMAL(5,3) DEFAULT 0.000,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ligne_contrat_contrat_id_foreign` (`contrat_id`),
  KEY `ligne_contrat_produit_id_foreign` (`produit_id`),
  CONSTRAINT `ligne_contrat_contrat_id_foreign` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ligne_contrat_produit_id_foreign` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
