ALTER TABLE `reglements`
ADD COLUMN `date_echeance_cheque` DATE NULL AFTER `banque`,
ADD COLUMN `statut_cheque` VARCHAR(30) NULL DEFAULT 'en_attente' AFTER `date_echeance_cheque`,
ADD COLUMN `image_cheque` VARCHAR(500) NULL AFTER `statut_cheque`;
