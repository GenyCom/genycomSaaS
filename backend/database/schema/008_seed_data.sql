-- ============================================================
-- GenyCom Web SaaS — Données de référence LOCALE (Tenant)
-- ============================================================

-- Ici, mettez uniquement des données propres à chaque client (ex: Etats documents, Types clients)
-- NE PAS mettre d'insertions pour les tables centralisées (users, roles, permissions)

-- Initialiser la devise
INSERT INTO `devise` (`id`, `nom`, `symbole`, `is_principale`) VALUES (1, 'Dirham Marocain', 'DH', 1);

-- Insertion des états de facture
INSERT INTO `etat_document`(`type_document`, `ordre`, `code`, `libelle`, `detail`, `couleur`, `is_system`) VALUES 
('facture', 1, 'BRL', 'Brouillon', 'Etat Initial de la facture lors de la création, n''est pas encore finalisée', '#94a3b8', 1),
('facture', 2, 'OVR', 'Ouverte', 'La facture est créée et envoyée au client en attente de règlement', '#3b82f6', 1),
('facture', 3, 'PAY', 'Payée', 'La facture a été réglée en totalité', '#10b981', 1),
('facture', 4, 'RTD', 'En retard', 'La facture n''a pas été réglée et que la date d''échéance est passée', '#f59e0b', 1),
('facture', 5, 'ANL', 'Annulée', 'La facture n''est pas acceptée par le client (Annulée)', '#ef4444', 1);

-- Insertion des Taux de TVA (Standard Maroc)
INSERT INTO `taux_tva`(`taux`, `libelle`, `detail`, `actif`) VALUES 
(20.000, 'TVA 20%', 'Taux standard applicable à la majorité des produits et services', 1),
(14.000, 'TVA 14%', 'Taux réduit', 1),
(10.000, 'TVA 10%', 'Taux réduit pour restauration, hébergement, etc.', 1),
(7.000, 'TVA 7%', 'Taux réduit spécifique (eau, électricité, etc.)', 1),
(0.000, 'TVA 0%', 'Exonéré ou hors champ', 1);

-- Magasin principal (Entrepôt par défaut)
INSERT INTO `entrepots`(`code`, `nom`, `adresse`, `is_default`) VALUES 
('MAG-001', 'Magasin Principal', 'Entrepôt central de l''entreprise', 1);

-- Familles de produits par défaut
INSERT INTO `famille_produit`(`code`, `libelle`, `detail`, `parent_id`) VALUES 
('MAR', 'Marchandises', 'Articles achetés pour être revendus en l''état', NULL),
('PF', 'Produits Finis', 'Produits fabriqués par l''entreprise', NULL),
('SERV', 'Services', 'Prestations de services et facturation immatérielle', NULL),
('CONS', 'Consommables', 'Fournitures et consommables internes', NULL);

-- Insertion des Conditions de Réglement
INSERT INTO `condition_reglement`(`libelle`, `detail`, `nombre_jours`) VALUES 
('Paiement immédiat', 'Paiment sur place sans attendre la fin de la journée', 0),
('Aujourd''hui', 'Paiment au cours de la journée', 1),
('15 jours', 'Paiment dans les 15 jours qui suivent', 15),
('Un mois', 'Paiment dans la limite d''un mois', 30),
('Autre', 'Spécifier une autre période de réglement', -1);

-- Insertion des états de dépenses
INSERT INTO `etat_document`(`type_document`, `ordre`, `code`, `libelle`, `detail`, `couleur`, `is_system`) VALUES 
('depense', 1, 'BRL', 'Brouillon', 'Etat initial d''une dépense', '#94a3b8', 1),
('depense', 2, 'PAY', 'Payée', 'La dépense a été réglée en totalité', '#10b981', 1),
('depense', 3, 'RTD', 'En retard', 'La dépense n''a pas été réglée et que la date d''échéance est passée', '#f59e0b', 1),
('depense', 4, 'ANL', 'Annulée', 'La dépense est annulée', '#ef4444', 1);

-- Insertion mode_reglement
INSERT INTO `mode_reglement`(`libelle`) VALUES 
('Espèces'), ('Chèque'), ('Carte bancaire'), ('Virement');

-- Mode de livraison
INSERT INTO `mode_livraison`(`libelle`) VALUES 
('Normal'), ('Recommandé'), ('Transporteur');

-- Etat de Commande
INSERT INTO `etat_document`(`type_document`, `ordre`, `code`, `libelle`, `detail`, `couleur`, `is_system`) VALUES 
('commande', 1, 'ENC', 'En cours', 'La commande est créée', '#94a3b8', 1),
('commande', 2, 'OVR', 'Ouverte', 'La commande est envoyée au fournisseur', '#3b82f6', 1),
('commande', 3, 'REC', 'Reçu', 'La commande est livrée', '#10b981', 1),
('commande', 4, 'ANL', 'Annulée', 'La commande est annulée', '#ef4444', 1);

-- Catégories dépenses
INSERT INTO `categorie_depense` (`id`, `libelle`, `detail`, `parent_id`) VALUES 
(1, 'Charges Variables', 'Charges variables', NULL),
(2, 'Charges Fixes', 'Charges fixes', NULL),
(3, 'Autres', 'Autres charges', NULL),
(4, 'Fête & Cadeaux', 'Cérémonies et fêtes', 1),
(5, 'Frais de sous-traitance', 'Les frais de sous-traitance', 1),
(6, 'Commissions', 'Commissions versées', 1),
(7, 'Achat marchandises', 'Achat de marchandises', 1),
(8, 'Loyer', 'Loyers ou immeubles', 2),
(9, 'Eau, Electricité', 'Factures fixes', 2),
(10, 'Frais de carburant', 'Transport', 2),
(11, 'Frais restauration', 'Restauration et hôtels', 2);

-- Type de client (Secteur / Domaine)
INSERT INTO `type_client`(`libelle`, `exempt_tva`, `vip`) VALUES 
('Particulier (B2C)', 0, 0),
('PME / TPE', 0, 0),
('Grand Compte', 0, 1),
('Administration Publique', 1, 0),
('Revendeur / Distributeur', 0, 0);

-- Type de fournisseur (Domaine / Catégorie)
INSERT INTO `type_fournisseur`(`libelle`, `detail`, `vip`) VALUES 
('Marchandises & Matières', 'Fournisseur de matières premières et produits finis', 0),
('Matériel & Équipement', 'Fournisseur d''équipements IT, machines, véhicules', 0),
('Services & Prestations', 'Prestataire de services (Marketing, Consulting, IT)', 0),
('Sous-traitant', 'Sous-traitance spécialisée ou BTP', 0),
('Logistique & Transport', 'Prestataire de fret, transport et stockage', 0),
('Fournitures (Consommables)', 'Fournitures de bureau, entretien, hygiène', 0);

-- Etat de Dette
INSERT INTO `etat_document`(`type_document`, `ordre`, `code`, `libelle`, `detail`, `couleur`, `is_system`) VALUES 
('dette', 1, 'BRL', 'Brouillon', 'Dette créée', '#94a3b8', 1),
('dette', 2, 'OVR', 'Ouverte', 'Dette validée en attente de règlement', '#3b82f6', 1),
('dette', 3, 'PPY', 'Partielle', 'Dette partiellement réglée', '#f59e0b', 1),
('dette', 4, 'PAY', 'Soldée', 'Dette intégralement réglée', '#10b981', 1),
('dette', 5, 'RTD', 'En retard', 'Echéance dépassée', '#ef4444', 1),
('dette', 6, 'ANL', 'Annulée', 'Dette annulée', '#64748b', 1);

INSERT INTO etat_document (type_document, code, libelle, color, ordre, is_system, tenant_id) 
VALUES 
('devis', 'BRO', 'Brouillon', '#64748b', 1, 1, 1),
('devis', 'ENV', 'Envoyé', '#3b82f6', 2, 1, 1),
('devis', 'ACC', 'Accepté', '#10b981', 3, 1, 1);
