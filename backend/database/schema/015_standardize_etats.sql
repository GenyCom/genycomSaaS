-- ============================================================
-- GenyCom Web SaaS — Standardisation des états (etat_id)
-- ============================================================

-- 1. Table bons_livraison
ALTER TABLE `bons_livraison` ADD COLUMN IF NOT EXISTS `etat_id` BIGINT UNSIGNED NULL AFTER `statut`;
ALTER TABLE `bons_livraison` ADD CONSTRAINT `fk_bl_etat` FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`) ON DELETE SET NULL;

-- 2. Table br (Bons de Réception)
ALTER TABLE `br` ADD COLUMN IF NOT EXISTS `etat_id` BIGINT UNSIGNED NULL AFTER `statut`;
ALTER TABLE `br` ADD CONSTRAINT `fk_br_etat` FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`) ON DELETE SET NULL;

-- 3. Table factures_achats
ALTER TABLE `factures_achats` ADD COLUMN IF NOT EXISTS `etat_id` BIGINT UNSIGNED NULL AFTER `statut`;
ALTER TABLE `factures_achats` ADD CONSTRAINT `fk_fa_etat` FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`) ON DELETE SET NULL;

-- 4. Table bcf (Bons de Commande Fournisseur)
ALTER TABLE `bcf` ADD COLUMN IF NOT EXISTS `etat_id` BIGINT UNSIGNED NULL AFTER `statut`;
ALTER TABLE `bcf` ADD CONSTRAINT `fk_bcf_etat` FOREIGN KEY (`etat_id`) REFERENCES `etat_document`(`id`) ON DELETE SET NULL;
