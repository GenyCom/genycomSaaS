-- ==============================================================================
-- SCRIPT DE MISE À JOUR SQL POUR PRODUCTION (BASE CENTRALE)
-- Date: 06 Septembre 2026
-- Description:
-- 1. Rendre tenant_user.role_id NULLable et passer role_id = NULL pour les Owners
-- 2. Injection des 23 permissions manquantes dans le dictionnaire central
-- ==============================================================================

-- 1. Rendre la colonne role_id NULLable pour permettre aux Owners de n'avoir aucun role_id
ALTER TABLE tenant_user MODIFY role_id BIGINT UNSIGNED NULL;

-- 2. Passer role_id à NULL pour tous les comptes Gérants / Owners existants
UPDATE tenant_user SET role_id = NULL WHERE is_owner = 1;

-- 3. Injection des permissions manquantes dans la table permissions (Dictionnaire Central)
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `module`) VALUES
('bons-commande-client.view', 'Voir les bons de commande client', 'ventes'),
('bons-commande-client.create', 'Créer un bon de commande client', 'ventes'),
('bons-commande-client.edit', 'Modifier un bon de commande client', 'ventes'),
('bons-commande-client.delete', 'Supprimer un bon de commande client', 'ventes'),
('bons-livraison.view', 'Voir les bons de livraison', 'ventes'),
('bons-livraison.create', 'Créer un bon de livraison', 'ventes'),
('bons-livraison.edit', 'Modifier un bon de livraison', 'ventes'),
('bons-livraison.delete', 'Supprimer un bon de livraison', 'ventes'),
('avoirs-clients.view', 'Voir les avoirs clients', 'ventes'),
('avoirs-clients.create', 'Créer un avoir client', 'ventes'),
('contrats.view', 'Voir les contrats & abonnements', 'ventes'),
('contrats.create', 'Créer un contrat', 'ventes'),
('contrats.edit', 'Modifier un contrat', 'ventes'),
('bons-reception.view', 'Voir les bons de réception', 'achats'),
('bons-reception.create', 'Créer un bon de réception', 'achats'),
('bons-reception.edit', 'Modifier un bon de réception', 'achats'),
('factures-achats.view', 'Voir les factures d''achat', 'achats'),
('factures-achats.create', 'Créer une facture d''achat', 'achats'),
('factures-achats.edit', 'Modifier une facture d''achat', 'achats'),
('avoirs-fournisseurs.view', 'Voir les avoirs fournisseurs', 'achats'),
('avoirs-fournisseurs.create', 'Créer un avoir fournisseur', 'achats'),
('reporting.view', 'Voir les rapports et analyses', 'reporting'),
('caisse.view', 'Voir la trésorerie et la caisse', 'finances');
