-- ============================================================
-- GenyCom Web SaaS — Données de référence (Seed)
-- ============================================================

-- Permissions système
INSERT INTO `permissions` (`name`, `display_name`, `module`) VALUES
-- Dashboard
('dashboard.view', 'Voir le tableau de bord', 'dashboard'),
-- Clients
('clients.view', 'Voir les clients', 'clients'),
('clients.create', 'Créer un client', 'clients'),
('clients.edit', 'Modifier un client', 'clients'),
('clients.delete', 'Supprimer un client', 'clients'),
-- Fournisseurs
('fournisseurs.view', 'Voir les fournisseurs', 'fournisseurs'),
('fournisseurs.create', 'Créer un fournisseur', 'fournisseurs'),
('fournisseurs.edit', 'Modifier un fournisseur', 'fournisseurs'),
('fournisseurs.delete', 'Supprimer un fournisseur', 'fournisseurs'),
-- Produits
('produits.view', 'Voir les produits', 'produits'),
('produits.create', 'Créer un produit', 'produits'),
('produits.edit', 'Modifier un produit', 'produits'),
('produits.delete', 'Supprimer un produit', 'produits'),
-- Devis
('devis.view', 'Voir les devis', 'ventes'),
('devis.create', 'Créer un devis', 'ventes'),
('devis.edit', 'Modifier un devis', 'ventes'),
('devis.delete', 'Supprimer un devis', 'ventes'),
('devis.transform', 'Transformer un devis en facture', 'ventes'),
-- Factures
('factures.view', 'Voir les factures', 'ventes'),
('factures.create', 'Créer une facture', 'ventes'),
('factures.edit', 'Modifier une facture', 'ventes'),
('factures.delete', 'Supprimer une facture', 'ventes'),
('factures.import', 'Importer des lignes de facture', 'ventes'),
-- Commandes
('commandes.view', 'Voir les commandes', 'achats'),
('commandes.create', 'Créer une commande', 'achats'),
('commandes.edit', 'Modifier une commande', 'achats'),
('commandes.delete', 'Supprimer une commande', 'achats'),
-- Stock
('stock.view', 'Voir le stock', 'stock'),
('stock.mouvement', 'Effectuer un mouvement de stock', 'stock'),
('stock.inventaire', 'Gérer les inventaires', 'stock'),
-- Finances
('reglements.view', 'Voir les règlements', 'finances'),
('reglements.create', 'Enregistrer un règlement', 'finances'),
('depenses.view', 'Voir les dépenses', 'finances'),
('depenses.create', 'Créer une dépense', 'finances'),
('dettes.view', 'Voir les dettes fournisseur', 'finances'),
-- Projets
('projets.view', 'Voir les projets', 'projets'),
('projets.create', 'Créer un projet', 'projets'),
('projets.edit', 'Modifier un projet', 'projets'),
-- Paramétrage
('parametrage.view', 'Voir les paramètres', 'parametrage'),
('parametrage.edit', 'Modifier les paramètres', 'parametrage'),
('users.manage', 'Gérer les utilisateurs', 'parametrage'),
('roles.manage', 'Gérer les rôles', 'parametrage');
