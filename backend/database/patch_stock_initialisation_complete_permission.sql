-- ==============================================================================
-- PATCH SQL : Ajout de la permission 'Initialisation complète du stock'
-- ==============================================================================

-- 1. Insertion de la nouvelle permission dans la table centralisée 'permissions'
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `module`) 
VALUES ('stock.initialisation_complete', 'Initialisation complète du stock', 'stock');

-- 2. Attribution automatique de cette permission aux rôles administrateurs / système
INSERT IGNORE INTO `permission_role` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
JOIN `permissions` p ON p.name = 'stock.initialisation_complete'
WHERE LOWER(r.name) IN ('admin', 'administrateur') OR r.is_system = 1;
