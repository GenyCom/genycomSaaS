-- ==============================================================================
-- PATCH SQL : Isolation des Rôles & Habilitations par Tenant
-- ==============================================================================

-- 1. Ajout de la colonne 'tenant_id' à la table centralisée 'roles'
ALTER TABLE `roles` 
ADD COLUMN `tenant_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 2. Clé étrangère vers la table 'tenants' (suppression en cascade si le tenant est supprimé)
ALTER TABLE `roles` 
ADD CONSTRAINT `fk_roles_tenant` 
FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE;

-- 3. Suppression de la contrainte d'unicité globale du champ 'name'
-- (Permet à chaque tenant d'avoir son propre rôle 'Commercial', 'Comptable', etc.)
ALTER TABLE `roles` DROP INDEX `name`;

-- (Optionnel) Index composite pour accélérer les recherches par tenant et nom de rôle
CREATE INDEX `idx_roles_tenant_name` ON `roles` (`tenant_id`, `name`);
