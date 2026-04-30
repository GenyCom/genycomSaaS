-- Fichier de données de démo COHÉRENT pour GenyCom SaaS
-- Tenant ID: 3
-- Ce script assure que chaque document a des lignes et que les totaux sont exacts.

-- Nettoyage préalable
DELETE FROM `ligne_facture` WHERE `tenant_id` = 3;
DELETE FROM `factures` WHERE `tenant_id` = 3;
DELETE FROM `ligne_bon_commande_client` WHERE `tenant_id` = 3;
DELETE FROM `bons_commande_client` WHERE `tenant_id` = 3;
DELETE FROM `ligne_devis` WHERE `tenant_id` = 3;
DELETE FROM `devis` WHERE `tenant_id` = 3;
DELETE FROM `mouvements_stock` WHERE `tenant_id` = 3;
DELETE FROM `stocks` WHERE `tenant_id` = 3;
DELETE FROM `produits` WHERE `tenant_id` = 3;
DELETE FROM `projets` WHERE `tenant_id` = 3;
DELETE FROM `clients` WHERE `tenant_id` = 3;
DELETE FROM `fournisseurs` WHERE `tenant_id` = 3;

-- 1. Tiers
INSERT INTO `fournisseurs` (`id`, `tenant_id`, `code_fournisseur`, `societe`, `ville`, `is_active`, `created_at`) VALUES
(1, 3, 'F-001', 'Tech Distribution', 'Casablanca', 1, NOW()),
(2, 3, 'F-002', 'Global Services', 'Casablanca', 1, NOW()),
(3, 3, 'F-003', 'Office Supply Co', 'Rabat', 1, NOW());

INSERT INTO `clients` (`id`, `tenant_id`, `code_client`, `societe`, `ville`, `is_active`, `created_at`) VALUES
(1, 3, 'C-001', 'Banque Populaire', 'Casablanca', 1, NOW()),
(2, 3, 'C-002', 'OCP Group', 'Casablanca', 1, NOW()),
(3, 3, 'C-003', 'Maroc Telecom', 'Rabat', 1, NOW()),
(4, 3, 'C-004', 'Managem', 'Casablanca', 1, NOW()),
(5, 3, 'C-005', 'Royal Air Maroc', 'Casablanca', 1, NOW());

-- 2. Produits
INSERT INTO `produits` (`id`, `tenant_id`, `reference`, `designation`, `prix_ht_achat`, `prix_ht_vente`, `taux_tva`, `is_actif`, `stock_actuel`, `created_at`) VALUES
(1, 3, 'REF-001', 'Laptop Pro X1', 8000.00, 10000.00, 20.00, 1, 50, NOW()),
(2, 3, 'REF-002', 'Ecran 27 Pouces', 1500.00, 2500.00, 20.00, 1, 30, NOW()),
(3, 3, 'REF-003', 'Clavier Mécanique', 400.00, 800.00, 20.00, 1, 100, NOW()),
(4, 3, 'REF-004', 'Serveur NAS 4To', 3500.00, 5500.00, 20.00, 1, 10, NOW()),
(5, 3, 'REF-005', 'Imprimante Laser', 1200.00, 1800.00, 20.00, 1, 20, NOW());

-- 3. Stock Initial
INSERT INTO `stocks` (`tenant_id`, `produit_id`, `entrepot_id`, `quantite`, `created_at`) VALUES
(3, 1, 1, 50, NOW()), (3, 2, 1, 30, NOW()), (3, 3, 1, 100, NOW()), (3, 4, 1, 10, NOW()), (3, 5, 1, 20, NOW());

-- 4. DEVIS (10 Devis)
INSERT INTO `devis` (`id`, `tenant_id`, `numero`, `client_id`, `date_devis`, `total_ht`, `total_tva`, `total_ttc`, `etat_id`, `created_at`)
SELECT 
    n, 3, CONCAT('DEV-2026-', LPAD(n, 3, '0')), (n % 5) + 1, 
    DATE_SUB('2026-04-30', INTERVAL (15 - n) DAY), 
    15000.00, 3000.00, 18000.00, 3, NOW()
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t;

INSERT INTO `ligne_devis` (`tenant_id`, `devis_id`, `produit_id`, `designation`, `quantite`, `prix_unitaire`, `taux_tva`, `montant_ht`, `montant_tva`, `montant_ttc`, `ordre`)
SELECT 3, n, 1, 'Laptop Pro X1', 1, 10000.00, 20.00, 10000.00, 2000.00, 12000.00, 1 FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t
UNION ALL
SELECT 3, n, 2, 'Ecran 27 Pouces', 2, 2500.00, 20.00, 5000.00, 1000.00, 6000.00, 2 FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t;

-- 5. BONS DE COMMANDE (10 BCC)
INSERT INTO `bons_commande_client` (`id`, `tenant_id`, `numero`, `client_id`, `date_commande`, `total_ht`, `total_tva`, `total_ttc`, `etat_id`, `created_at`)
SELECT 
    n, 3, CONCAT('BCC-2026-', LPAD(n, 3, '0')), (n % 5) + 1, 
    DATE_SUB('2026-04-30', INTERVAL (20 - n) DAY), 
    15000.00, 3000.00, 18000.00, 2, NOW()
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t;

INSERT INTO `ligne_bon_commande_client` (`tenant_id`, `bon_commande_client_id`, `produit_id`, `designation`, `quantite`, `prix_unitaire`, `taux_tva`, `montant_ht`, `montant_tva`, `montant_ttc`, `ordre`)
SELECT 3, n, 1, 'Laptop Pro X1', 1, 10000.00, 20.00, 10000.00, 2000.00, 12000.00, 1 FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t
UNION ALL
SELECT 3, n, 2, 'Ecran 27 Pouces', 2, 2500.00, 20.00, 5000.00, 1000.00, 6000.00, 2 FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t;

-- 6. FACTURES (10 Factures)
INSERT INTO `factures` (`id`, `tenant_id`, `numero`, `client_id`, `date_facture`, `total_ht`, `total_tva`, `total_ttc`, `montant_regle`, `montant_restant`, `etat_id`, `est_reglee`, `created_at`)
SELECT 
    n, 3, CONCAT('FAC-2026-', LPAD(n, 3, '0')), (n % 5) + 1, 
    DATE_SUB('2026-04-30', INTERVAL (25 - n) DAY), 
    15000.00, 3000.00, 18000.00, 
    IF(n <= 5, 18000.00, 0.00), 
    IF(n <= 5, 0.00, 18000.00),
    IF(n <= 5, 3, 2), 
    IF(n <= 5, 1, 0),
    NOW()
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t;

INSERT INTO `ligne_facture` (`tenant_id`, `facture_id`, `produit_id`, `designation`, `quantite`, `prix_unitaire`, `taux_tva`, `montant_ht`, `montant_tva`, `montant_ttc`, `ordre`)
SELECT 3, n, 1, 'Laptop Pro X1', 1, 10000.00, 20.00, 10000.00, 2000.00, 12000.00, 1 FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t
UNION ALL
SELECT 3, n, 2, 'Ecran 27 Pouces', 2, 2500.00, 20.00, 5000.00, 1000.00, 6000.00, 2 FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) t;

-- 7. États pour le Dashboard et Workflows
DELETE FROM `etat_document` WHERE `tenant_id` = 3 AND `code` IN ('ACC', 'FAC', 'VAL', 'PAY', 'BRL', 'CONF', 'LIV');
INSERT INTO `etat_document` (`tenant_id`, `type_document`, `code`, `libelle`, `ordre`, `couleur`, `is_system`) VALUES
(3, 'devis', 'ACC', 'Accepté', 3, '#28a745', 1),
(3, 'devis', 'FAC', 'Facturé', 6, '#17a2b8', 1),
(3, 'facture', 'OVR', 'Ouverte', 2, '#17a2b8', 1),
(3, 'facture', 'PAY', 'Payée', 3, '#28a745', 1),
(3, 'bcc', 'BRL', 'Brouillon', 1, '#6c757d', 1),
(3, 'bcc', 'CONF', 'Confirmé', 2, '#007bff', 1),
(3, 'bcc', 'LIV', 'Livré', 3, '#28a745', 1),
(3, 'bcc', 'FAC', 'Facturé', 4, '#17a2b8', 1),
(3, 'avoir_client', 'BRL', 'Brouillon', 1, '#6c757d', 1),
(3, 'avoir_client', 'VAL', 'Validé', 2, '#28a745', 1);
