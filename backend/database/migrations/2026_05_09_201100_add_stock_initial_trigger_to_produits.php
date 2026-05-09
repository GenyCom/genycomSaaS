<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Trigger BEFORE pour synchroniser stock_actuel avec stock_initial
        DB::connection('tenant')->unprepared("
            CREATE TRIGGER trg_produit_stock_initial_before 
            BEFORE INSERT ON produits 
            FOR EACH ROW 
            BEGIN
                IF NEW.is_service = 0 AND NEW.stock_initial > 0 THEN
                    SET NEW.stock_actuel = NEW.stock_initial;
                END IF;
            END;
        ");

        // 2. Trigger AFTER pour créer les enregistrements liés (stocks et mouvements)
        DB::connection('tenant')->unprepared("
            CREATE TRIGGER trg_produit_stock_initial_after 
            AFTER INSERT ON produits 
            FOR EACH ROW 
            BEGIN
                IF NEW.is_service = 0 AND NEW.stock_initial > 0 THEN
                    -- Récupérer l'entrepôt par défaut pour ce tenant
                    SET @entrepot_id = (SELECT id FROM entrepots WHERE tenant_id = NEW.tenant_id AND is_default = 1 LIMIT 1);
                    
                    IF @entrepot_id IS NULL THEN
                        SET @entrepot_id = (SELECT id FROM entrepots WHERE tenant_id = NEW.tenant_id LIMIT 1);
                    END IF;
                    
                    IF @entrepot_id IS NOT NULL THEN
                        -- Créer l'entrée dans la table 'stocks'
                        INSERT INTO stocks (tenant_id, produit_id, entrepot_id, quantite, created_at, updated_at)
                        VALUES (NEW.tenant_id, NEW.id, @entrepot_id, NEW.stock_initial, NOW(), NOW());
                        
                        SET @stock_id = LAST_INSERT_ID();
                        
                        -- Créer le mouvement de stock
                        INSERT INTO mouvements_stocks (
                            tenant_id, stock_id, produit_id, type_mouvement, 
                            quantite, document_type, document_id, 
                            date_mouvement, libelle, created_at, updated_at, utilisateur_id
                        )
                        VALUES (
                            NEW.tenant_id, @stock_id, NEW.id, 'ENTREE', 
                            NEW.stock_initial, 'INITIAL', NEW.id, 
                            NOW(), 'Stock Initial (Initialisation automatique)', NOW(), NOW(), NEW.created_by
                        );
                    END IF;
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('tenant')->unprepared("DROP TRIGGER IF EXISTS trg_produit_stock_initial_before");
        DB::connection('tenant')->unprepared("DROP TRIGGER IF EXISTS trg_produit_stock_initial_after");
    }
};
