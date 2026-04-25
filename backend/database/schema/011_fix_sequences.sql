-- ============================================================
-- GenyCom Web SaaS — Fix Sequences Numerotation (Multi-tenant)
-- ============================================================

-- Ajout de la colonne tenant_id si absente
ALTER TABLE `sequences_numerotation` ADD COLUMN IF NOT EXISTS `tenant_id` BIGINT UNSIGNED NOT NULL AFTER `id`;

-- Mise à jour de l'unique key pour inclure le tenant
ALTER TABLE `sequences_numerotation` DROP INDEX IF EXISTS `uk_seq`;
ALTER TABLE `sequences_numerotation` ADD UNIQUE KEY `uk_seq_tenant` (`tenant_id`, `type_document`, `prefixe`, `annee`, `mois`);

-- Liaison foreign key (Supprimé car la table 'tenants' est en base centrale, pas en base tenant)
-- ALTER TABLE `sequences_numerotation` ADD CONSTRAINT `fk_seq_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`);
