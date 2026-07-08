ALTER TABLE `reglements`
ADD COLUMN `date_echeance_cheque` DATE NULL AFTER `banque`,
ADD COLUMN `statut_cheque` VARCHAR(30) NULL DEFAULT 'en_attente' AFTER `date_echeance_cheque`,
ADD COLUMN `image_cheque` VARCHAR(500) NULL AFTER `statut_cheque`;

-- =====================================================================
-- PATCH SQL : AJOUT DES RÉFÉRENCES OEM ET COMPATIBLES (ALTERNATIVES)
-- =====================================================================

-- 1. Ajout de la colonne 'reference_oem' dans la table 'produits'
ALTER TABLE `produits` 
ADD COLUMN `reference_oem` VARCHAR(100) NULL DEFAULT NULL 
AFTER `reference_fournisseur`;

-- 2. Création de la table de pivot 'produit_compatibles' pour les liaisons d'alternatives
CREATE TABLE IF NOT EXISTS `produit_compatibles` (
    `produit_id` BIGINT UNSIGNED NOT NULL,
    `compatible_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`produit_id`, `compatible_id`),
    CONSTRAINT `fk_produit_compatibles_produit` 
        FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_produit_compatibles_compatible` 
        FOREIGN KEY (`compatible_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

