-- =====================================================================
-- PATCH SQL : RETRAIT DE LA REMISE CLIENT ET RETRAIT DES COMPOSANTS MARGES/ACHATS
-- A exécuter sur chaque base de données de tenant
-- =====================================================================

-- 1. Ajout de la colonne de remise par défaut dans la table 'clients'
ALTER TABLE `clients` ADD COLUMN IF NOT EXISTS `taux_remise` DECIMAL(5,2) NULL DEFAULT 0.00 AFTER `solde_initial`;

-- 2. Nettoyage des colonnes marge_minimale et marge_maximale
ALTER TABLE `clients` DROP COLUMN IF EXISTS `marge_minimale`, DROP COLUMN IF EXISTS `marge_maximale`;

-- 3. Nettoyage de la colonne 'prix_achat' dans les tables de lignes de ventes
ALTER TABLE `ligne_devis` DROP COLUMN IF EXISTS `prix_achat`;
ALTER TABLE `ligne_facture` DROP COLUMN IF EXISTS `prix_achat`;
ALTER TABLE `ligne_bon_commande_client` DROP COLUMN IF EXISTS `prix_achat`;
ALTER TABLE `ligne_bon_livraison` DROP COLUMN IF EXISTS `prix_achat`;

-- 4. Option client par défaut pour ventes comptoir
ALTER TABLE `clients` ADD COLUMN IF NOT EXISTS `is_default` TINYINT(1) NULL DEFAULT 0 AFTER `is_active`;
