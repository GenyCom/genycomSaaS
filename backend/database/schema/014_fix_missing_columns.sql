-- ============================================================
-- GenyCom Web SaaS — Fix colonnes manquantes (projet_id, entrepot_id)
-- ============================================================

-- 1. Table bcf (Commandes Fournisseur) : ajouter projet_id et entrepot_id
ALTER TABLE `bcf` ADD COLUMN IF NOT EXISTS `projet_id` BIGINT UNSIGNED NULL AFTER `fournisseur_id`;
ALTER TABLE `bcf` ADD COLUMN IF NOT EXISTS `entrepot_id` BIGINT UNSIGNED NULL AFTER `projet_id`;

-- 2. Table bons_commande_client (BCC) : ajouter entrepot_id
ALTER TABLE `bons_commande_client` ADD COLUMN IF NOT EXISTS `entrepot_id` BIGINT UNSIGNED NULL AFTER `devis_id`;

ALTER TABLE `clients` ADD COLUMN solde_initial DECIMAL(15,2) DEFAULT 0.00 AFTER delai_paiement;

ALTER TABLE stocks ADD UNIQUE KEY uk_produit_entrepot_tenant (produit_id, entrepot_id, tenant_id);

DELIMITER //

CREATE TRIGGER trg_produit_stock_initial_before 
BEFORE INSERT ON produits 
FOR EACH ROW 
BEGIN
    -- On ne traite que si c'est un produit physique (pas un service) et que le stock initial est défini
    IF NEW.is_service = 0 AND NEW.stock_initial > 0 THEN
        SET NEW.stock_actuel = NEW.stock_initial;
    END IF;
END //

DELIMITER ;


DELIMITER //

DROP TRIGGER IF EXISTS trg_produit_stock_initial_after; //

CREATE TRIGGER trg_produit_stock_initial_after 
AFTER INSERT ON produits 
FOR EACH ROW 
BEGIN
    IF NEW.is_service = 0 AND NEW.stock_initial > 0 THEN
        SET @ent_id = (SELECT id FROM entrepots WHERE tenant_id = NEW.tenant_id AND is_default = 1 LIMIT 1);
        IF @ent_id IS NULL THEN SET @ent_id = (SELECT id FROM entrepots WHERE tenant_id = NEW.tenant_id LIMIT 1); END IF;
        
        IF @ent_id IS NOT NULL THEN
            -- Utilisation de ON DUPLICATE KEY UPDATE pour éviter les erreurs et les doublons
            INSERT INTO stocks (tenant_id, produit_id, entrepot_id, quantite, created_at, updated_at)
            VALUES (NEW.tenant_id, NEW.id, @ent_id, NEW.stock_initial, NOW(), NOW())
            ON DUPLICATE KEY UPDATE quantite = quantite + NEW.stock_initial, updated_at = NOW();
            
            SET @s_id = (SELECT id FROM stocks WHERE produit_id = NEW.id AND entrepot_id = @ent_id LIMIT 1);
            
            INSERT INTO mouvements_stock (
                tenant_id, stock_id, produit_id, type_mouvement, 
                quantite, document_type, document_id, 
                libelle, created_at, created_by
            )
            VALUES (
                NEW.tenant_id, @s_id, NEW.id, 'ajustement_positif', 
                NEW.stock_initial, 'INITIAL', NEW.id, 
                'Stock Initial (Initialisation automatique)', NOW(), NEW.created_by
            );
        END IF;
    END IF;
END //

DELIMITER ;
