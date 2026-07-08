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

-- =====================================================================
-- EXEMPLE DE DONNÉES DE DÉMONSTRATION (Seeding sur le tenant_id = 1)
-- =====================================================================

-- Mise à jour du produit Brembo (Rupture de stock et OEM configuré)
UPDATE `produits` 
SET `reference_oem` = '04465-0D040', `stock_actuel` = 0.00 
WHERE `reference` = 'PLAQ-FRN-BREM' AND `tenant_id` = 1;

-- Insertion de l'alternative Valeo (En stock, même OEM)
INSERT INTO `produits` 
(`tenant_id`, `famille_id`, `reference`, `designation`, `marque`, `prix_ht_achat`, `prix_ht_vente`, `taux_tva`, `prix_ttc_vente`, `stock_actuel`, `stock_initial`, `seuil_alerte`, `unite`, `is_service`, `is_actif`, `reference_oem`, `created_at`) 
VALUES
(1, (SELECT `id` FROM `famille_produit` WHERE `libelle` = 'Plaquettes' LIMIT 1), 'PLAQ-FRN-VALEO', 'Plaquettes de frein Valeo V202', 'Valeo', 190.00, 320.00, 20.00, 384.00, 15.00, 15.00, 4, 'Jeu', 0, 1, '04465-0D040', NOW())
ON DUPLICATE KEY UPDATE `reference_oem` = '04465-0D040', `stock_actuel` = 15.00;

-- Liaison de compatibilité bidirectionnelle (Brembo <-> Valeo)
INSERT IGNORE INTO `produit_compatibles` (`produit_id`, `compatible_id`)
SELECT p1.id, p2.id
FROM `produits` p1, `produits` p2
WHERE p1.reference = 'PLAQ-FRN-BREM' AND p2.reference = 'PLAQ-FRN-VALEO' AND p1.tenant_id = 1 AND p2.tenant_id = 1;

INSERT IGNORE INTO `produit_compatibles` (`produit_id`, `compatible_id`)
SELECT p1.id, p2.id
FROM `produits` p1, `produits` p2
WHERE p1.reference = 'PLAQ-FRN-VALEO' AND p2.reference = 'PLAQ-FRN-BREM' AND p1.tenant_id = 1 AND p2.tenant_id = 1;
