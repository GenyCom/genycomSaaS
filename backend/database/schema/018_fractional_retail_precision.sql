-- ============================================================
-- GenyCom Web SaaS — Patch SQL : Précision Vente au Détail / Pesée / Vrac (3 Décimales)
-- Permet la vente et la gestion de stock au millième (ex: 0.100 kg, 0.125 kg, 0.250 L, 0.050 m)
-- ============================================================

-- 1. Table des Stocks physiques
ALTER TABLE `stocks` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 0.000;

-- 2. Table des Mouvements de stock
ALTER TABLE `mouvements_stock` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 0.000;

-- 3. Table des Produits (Niveaux de stocks & Seuils)
ALTER TABLE `produits` 
    MODIFY COLUMN `stock_actuel` DECIMAL(24, 3) NOT NULL DEFAULT 0.000,
    MODIFY COLUMN `stock_initial` DECIMAL(24, 3) NOT NULL DEFAULT 0.000,
    MODIFY COLUMN `seuil_alerte` DECIMAL(24, 3) NULL DEFAULT 0.000,
    MODIFY COLUMN `stock_min` DECIMAL(24, 3) NULL DEFAULT 0.000,
    MODIFY COLUMN `stock_max` DECIMAL(24, 3) NULL DEFAULT NULL,
    MODIFY COLUMN `poids` DECIMAL(24, 3) NULL DEFAULT NULL;

-- 4. Ventes : Lignes de Factures (POS / Caisse & Facturation Standard)
ALTER TABLE `ligne_facture` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 5. Ventes : Bons de Livraison (BL)
ALTER TABLE `ligne_bon_livraison` 
    MODIFY COLUMN `quantite_prevue` DECIMAL(24, 3) NOT NULL DEFAULT 0.000,
    MODIFY COLUMN `quantite_livree` DECIMAL(24, 3) NOT NULL DEFAULT 0.000;

-- Ajouter la colonne unité si absente sur ligne_bon_livraison
ALTER TABLE `ligne_bon_livraison` 
    ADD COLUMN IF NOT EXISTS `unite` VARCHAR(50) NULL AFTER `designation`;

-- 6. Ventes : Devis
ALTER TABLE `ligne_devis` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 7. Ventes : Bons de Commande Client (BCC)
ALTER TABLE `ligne_bon_commande_client` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 8. Ventes : Avoirs Client
ALTER TABLE `ligne_avoir_client` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 9. Production / Nomenclature / Assemblages
ALTER TABLE `nomenclature_produit` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 10. Achats : Lignes Factures Achats
ALTER TABLE `facture_achat_lignes` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 11. Achats : Bons de Commande Fournisseur (BCF)
ALTER TABLE `bcf_lignes` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 12. Achats : Bons de Réception (BR)
ALTER TABLE `br_lignes` 
    MODIFY COLUMN `quantite_commandee` DECIMAL(24, 3) NOT NULL DEFAULT 0.000,
    MODIFY COLUMN `quantite_recue` DECIMAL(24, 3) NOT NULL DEFAULT 0.000;

-- 13. Achats : Avoirs Achats
ALTER TABLE `avoir_achat_lignes` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;

-- 14. Inventaire : Lignes d'inventaire
ALTER TABLE `inventaire_lignes` 
    MODIFY COLUMN `stock_theorique` DECIMAL(24, 3) NOT NULL DEFAULT 0.000,
    MODIFY COLUMN `stock_physique` DECIMAL(24, 3) NOT NULL DEFAULT 0.000,
    MODIFY COLUMN `ecart` DECIMAL(24, 3) NOT NULL DEFAULT 0.000;

-- 15. Contrats récurrents
ALTER TABLE `ligne_contrat` 
    MODIFY COLUMN `quantite` DECIMAL(24, 3) NOT NULL DEFAULT 1.000;
