-- ============================================================
-- GenyCom Web SaaS — Cycle d'Achat (OBSOLÈTE -> Remplacé par 012_procurement_overhaul.sql)
-- ============================================================
-- Ce fichier est conservé comme archive. 
-- Les définitions des tables BCF, BR, Factures Achats et Avoirs sont centralisées dans 012.

CREATE TABLE `dettes_fournisseur` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `numero` varchar(255) NOT NULL,
  `fournisseur_id` bigint(20) unsigned NOT NULL,
  `facture_id` bigint(20) unsigned DEFAULT NULL,
  `bon_reception_id` bigint(20) unsigned DEFAULT NULL,
  `date_dette` date NOT NULL,
  `date_echeance` date DEFAULT NULL,
  `montant_ht` decimal(15,2) NOT NULL DEFAULT 0.00,
  `montant_tva` decimal(15,2) NOT NULL DEFAULT 0.00,
  `montant_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `montant_restant` decimal(15,2) NOT NULL DEFAULT 0.00,
  `montant_regle` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dettes_fournisseur_numero_unique` (`numero`),
  KEY `dettes_fournisseur_tenant_id_index` (`tenant_id`),
  KEY `dettes_fournisseur_fournisseur_id_index` (`fournisseur_id`),
  KEY `dettes_fournisseur_facture_id_index` (`facture_id`),
  KEY `dettes_fournisseur_bon_reception_id_index` (`bon_reception_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;