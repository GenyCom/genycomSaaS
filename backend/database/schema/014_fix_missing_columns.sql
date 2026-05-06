-- ============================================================
-- GenyCom Web SaaS — Fix colonnes manquantes (projet_id, entrepot_id)
-- ============================================================

-- 1. Table bcf (Commandes Fournisseur) : ajouter projet_id et entrepot_id
ALTER TABLE `bcf` ADD COLUMN IF NOT EXISTS `projet_id` BIGINT UNSIGNED NULL AFTER `fournisseur_id`;
ALTER TABLE `bcf` ADD COLUMN IF NOT EXISTS `entrepot_id` BIGINT UNSIGNED NULL AFTER `projet_id`;

-- 2. Table bons_commande_client (BCC) : ajouter entrepot_id
ALTER TABLE `bons_commande_client` ADD COLUMN IF NOT EXISTS `entrepot_id` BIGINT UNSIGNED NULL AFTER `devis_id`;
