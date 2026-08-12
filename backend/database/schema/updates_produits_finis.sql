-- SQL Updates for finished product / composite product integration

-- 0. Table: produit_fini
ALTER TABLE `produit_fini` ADD COLUMN `created_by` BIGINT UNSIGNED NULL AFTER `prix_ttc`;

-- 1. Table: ligne_devis
ALTER TABLE `ligne_devis` MODIFY `produit_id` BIGINT UNSIGNED NULL;
ALTER TABLE `ligne_devis` ADD COLUMN `produit_fini_id` BIGINT UNSIGNED NULL AFTER `produit_id`;
ALTER TABLE `ligne_devis` ADD COLUMN `is_produit_fini` TINYINT(1) NOT NULL DEFAULT 0 AFTER `produit_fini_id`;
ALTER TABLE `ligne_devis` ADD CONSTRAINT `fk_ligne_devis_produit_fini` FOREIGN KEY (`produit_fini_id`) REFERENCES `produit_fini`(`id`) ON DELETE SET NULL;

-- 2. Table: ligne_facture
ALTER TABLE `ligne_facture` MODIFY `produit_id` BIGINT UNSIGNED NULL;
ALTER TABLE `ligne_facture` ADD COLUMN `produit_fini_id` BIGINT UNSIGNED NULL AFTER `produit_id`;
ALTER TABLE `ligne_facture` ADD COLUMN `is_produit_fini` TINYINT(1) NOT NULL DEFAULT 0 AFTER `produit_fini_id`;
ALTER TABLE `ligne_facture` ADD CONSTRAINT `fk_ligne_facture_produit_fini` FOREIGN KEY (`produit_fini_id`) REFERENCES `produit_fini`(`id`) ON DELETE SET NULL;

-- 3. Table: ligne_bon_livraison
ALTER TABLE `ligne_bon_livraison` MODIFY `produit_id` BIGINT UNSIGNED NULL;
ALTER TABLE `ligne_bon_livraison` ADD COLUMN `produit_fini_id` BIGINT UNSIGNED NULL AFTER `produit_id`;
ALTER TABLE `ligne_bon_livraison` ADD COLUMN `is_produit_fini` TINYINT(1) NOT NULL DEFAULT 0 AFTER `produit_fini_id`;
ALTER TABLE `ligne_bon_livraison` ADD CONSTRAINT `fk_ligne_bon_livraison_produit_fini` FOREIGN KEY (`produit_fini_id`) REFERENCES `produit_fini`(`id`) ON DELETE SET NULL;
