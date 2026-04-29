-- ============================================================
-- GenyCom Web SaaS — Fix Entreprise Table (Missing Columns)
-- ============================================================

-- Rename 'if' to 'if_fiscal' if it exists and 'if_fiscal' does not
SET @dbname = DATABASE();
SET @tablename = 'entreprise';
SET @columnname = 'if';
SET @newcolumnname = 'if_fiscal';

-- Check if 'if' exists and 'if_fiscal' does not
SELECT count(*) INTO @exists FROM information_schema.columns 
WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname;

SELECT count(*) INTO @new_exists FROM information_schema.columns 
WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @newcolumnname;

SET @s = IF(@exists > 0 AND @new_exists = 0, 
    'ALTER TABLE `entreprise` CHANGE `if` `if_fiscal` VARCHAR(50) NULL', 
    'SELECT "Column if already renamed or if_fiscal already exists"');
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add other missing columns
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `cnss` VARCHAR(50) NULL AFTER `patente`;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `fax` VARCHAR(50) NULL AFTER `telephone`;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `rib` VARCHAR(100) NULL AFTER `site_web`;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `banque` VARCHAR(100) NULL AFTER `rib`;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `devise_id` BIGINT UNSIGNED NULL AFTER `banque`;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `exercice_debut` TINYINT DEFAULT 1 AFTER `devise_id`;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `code_postal` VARCHAR(20) NULL AFTER `ville`;

ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `entete_impression` TEXT NULL;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `pied_page_impression` TEXT NULL;
ALTER TABLE `entreprise` ADD COLUMN IF NOT EXISTS `conditions_generales` TEXT NULL;
