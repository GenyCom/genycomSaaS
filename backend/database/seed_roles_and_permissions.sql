-- ==============================================================================
-- SCRIPT SQL : Initialisation complète des tables roles, permissions & permission_role
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Initialisation des Rôles Système Modèles (tenant_id = NULL, is_system = 1)
INSERT IGNORE INTO `roles` (`id`, `tenant_id`, `name`, `description`, `is_system`) VALUES 
(1, NULL, 'admin', 'Administrateur complet du tenant', 1),
(2, NULL, 'utilisateur', 'Utilisateur standard', 1);

-- 2. Initialisation du Dictionnaire Central des Permissions
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `module`) VALUES
-- Tableau de Bord
('dashboard.view', 'Voir le tableau de bord', 'dashboard'),

-- Clients & Fournisseurs
('clients.view', 'Voir les clients', 'clients'),
('clients.create', 'Créer un client', 'clients'),
('clients.edit', 'Modifier un client', 'clients'),
('clients.delete', 'Supprimer un client', 'clients'),
('fournisseurs.view', 'Voir les fournisseurs', 'fournisseurs'),
('fournisseurs.create', 'Créer un fournisseur', 'fournisseurs'),
('fournisseurs.edit', 'Modifier un fournisseur', 'fournisseurs'),
('fournisseurs.delete', 'Supprimer un fournisseur', 'fournisseurs'),

-- Produits & Catalogue
('produits.view', 'Voir les produits', 'produits'),
('produits.create', 'Créer un produit', 'produits'),
('produits.edit', 'Modifier un produit', 'produits'),
('produits.delete', 'Supprimer un produit', 'produits'),

-- Ventes
('devis.view', 'Voir les devis', 'ventes'),
('devis.create', 'Créer un devis', 'ventes'),
('devis.edit', 'Modifier un devis', 'ventes'),
('devis.delete', 'Supprimer un devis', 'ventes'),
('devis.transform', 'Transformer un devis en facture', 'ventes'),
('bons-commande-client.view', 'Voir les bons de commande client', 'ventes'),
('bons-commande-client.create', 'Créer un bon de commande client', 'ventes'),
('bons-commande-client.edit', 'Modifier un bon de commande client', 'ventes'),
('bons-commande-client.delete', 'Supprimer un bon de commande client', 'ventes'),
('bons-livraison.view', 'Voir les bons de livraison', 'ventes'),
('bons-livraison.create', 'Créer un bon de livraison', 'ventes'),
('bons-livraison.edit', 'Modifier un bon de livraison', 'ventes'),
('bons-livraison.delete', 'Supprimer un bon de livraison', 'ventes'),
('factures.view', 'Voir les factures', 'ventes'),
('factures.create', 'Créer une facture', 'ventes'),
('factures.edit', 'Modifier une facture', 'ventes'),
('factures.delete', 'Supprimer une facture', 'ventes'),
('factures.import', 'Importer des lignes de facture', 'ventes'),
('avoirs-clients.view', 'Voir les avoirs clients', 'ventes'),
('avoirs-clients.create', 'Créer un avoir client', 'ventes'),
('contrats.view', 'Voir les contrats & abonnements', 'ventes'),
('contrats.create', 'Créer un contrat', 'ventes'),
('contrats.edit', 'Modifier un contrat', 'ventes'),

-- Achats
('commandes.view', 'Voir les commandes fournisseurs', 'achats'),
('commandes.create', 'Créer une commande fournisseur', 'achats'),
('commandes.edit', 'Modifier une commande fournisseur', 'achats'),
('commandes.delete', 'Supprimer une commande fournisseur', 'achats'),
('bons-reception.view', 'Voir les bons de réception', 'achats'),
('bons-reception.create', 'Créer un bon de réception', 'achats'),
('bons-reception.edit', 'Modifier un bon de réception', 'achats'),
('factures-achats.view', 'Voir les factures d''achat', 'achats'),
('factures-achats.create', 'Créer une facture d''achat', 'achats'),
('factures-achats.edit', 'Modifier une facture d''achat', 'achats'),
('avoirs-fournisseurs.view', 'Voir les avoirs fournisseurs', 'achats'),
('avoirs-fournisseurs.create', 'Créer un avoir fournisseur', 'achats'),

-- Stock & Logistique
('stock.view', 'Voir le stock', 'stock'),
('stock.mouvement', 'Effectuer un mouvement de stock', 'stock'),
('stock.inventaire', 'Gérer les inventaires', 'stock'),
('stock.initialisation_complete', 'Initialisation complète du stock', 'stock'),

-- Finances & Trésorerie
('reglements.view', 'Voir les règlements', 'finances'),
('reglements.create', 'Enregistrer un règlement', 'finances'),
('depenses.view', 'Voir les dépenses', 'finances'),
('depenses.create', 'Créer une dépense', 'finances'),
('dettes.view', 'Voir les dettes fournisseur', 'finances'),
('caisse.view', 'Voir la trésorerie et la caisse', 'finances'),

-- Projets
('projets.view', 'Voir les projets', 'projets'),
('projets.create', 'Créer un projet', 'projets'),
('projets.edit', 'Modifier un projet', 'projets'),

-- Paramétrage & Habilitations
('parametrage.view', 'Voir les paramètres', 'parametrage'),
('parametrage.edit', 'Modifier les paramètres', 'parametrage'),
('users.manage', 'Gérer les utilisateurs', 'parametrage'),
('roles.manage', 'Gérer les rôles', 'parametrage'),

-- Reporting & Rapports
('reporting.view', 'Voir les rapports et analyses', 'reporting');

-- 3. Association de TOUTES les permissions au Rôle Admin (role_id = 1)
INSERT IGNORE INTO `permission_role` (`role_id`, `permission_id`)
SELECT 1, `id` FROM `permissions`;

-- 4. Association des permissions de base au Rôle Utilisateur Standard (role_id = 2)
INSERT IGNORE INTO `permission_role` (`role_id`, `permission_id`)
SELECT 2, `id` FROM `permissions`
WHERE `name` IN (
    'dashboard.view',
    'clients.view', 'fournisseurs.view', 'produits.view',
    'devis.view', 'devis.create',
    'bons-commande-client.view', 'bons-livraison.view', 'factures.view',
    'commandes.view', 'bons-reception.view',
    'stock.view', 'stock.mouvement',
    'reglements.view', 'depenses.view', 'projets.view'
);

SET FOREIGN_KEY_CHECKS = 1;
