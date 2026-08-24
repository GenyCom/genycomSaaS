-- ==============================================================================
-- GENYCOM SAAS — SCHEMA CENTRAL (genycom_central)
-- Ce schéma gère l'authentification globale, les tenants (Bases SaaS),
-- les rôles, les permissions, et la liaison Utilisateur <-> Tenant.
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. TENANTS (Les environnements SaaS)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
	db_username VARCHAR(100) NULL,
	db_password VARCHAR(100) NULL,
    database_name VARCHAR(100) NOT NULL UNIQUE, -- ex: genycom_client_001
    domain VARCHAR(255) NULL UNIQUE,            -- ex: client.genycom.ma
    logo VARCHAR(255) NULL,
    statut ENUM('actif', 'suspendu', 'demo') DEFAULT 'actif',
    plan VARCHAR(50) DEFAULT 'Business',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. USERS (Utilisateurs centraux)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(30) NULL,
    avatar_path VARCHAR(255) NULL,
    is_superadmin TINYINT(1) DEFAULT 0, -- 1 pour le boss genycomc@gmail.com
    is_active TINYINT(1) DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    last_seen_at TIMESTAMP NULL,
    last_seen_ip VARCHAR(45) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_users_last_seen_at (last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. PERMISSIONS & ROLES (Dictionnaire Central)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,     -- ex: 'facture.create'
    display_name VARCHAR(255) NULL,
    description VARCHAR(255) NULL,
    module VARCHAR(50) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,     -- ex: 'admin', 'commercial'
    description VARCHAR(255) NULL,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permission_role (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. PIVOT: USER_TENANT (Qui a accès à quelle Base SaaS et avec quel rôle ?)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tenant_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL, -- Le rôle du user spécifique à ce SaaS
    is_owner TINYINT(1) DEFAULT 0,    -- Si c'est le gérant du SaaS
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY tenant_user_unique (tenant_id, user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PERSONAL ACCESS TOKENS (Sanctum Tokens Centralisés)
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. CACHE & THROTTLE (Requis pour le RateLimiter et le Cache DB)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cache (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cache_locks (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- INSERTION DU SUPER ADMIN
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO users (nom, prenom, email, password, is_superadmin) 
VALUES ('Com', 'Geny', 'genycomc@gmail.com', '$2y$12$te5iCmRwTmrGf6u/jOuI4.P8xGGd1iLKNHHZQTUaG3i0wXbUmWTUm', 1); 
-- Mdp: password (hashé avec bcrypt, vérifié OK)

-- ------------------------------------------------------------------------------
-- INSERTION DES RÔLES SYSTÈME PAR DÉFAUT
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO roles (id, name, description, is_system) VALUES 
(1, 'admin', 'Administrateur complet du tenant', 1),
(2, 'utilisateur', 'Utilisateur standard', 1);

-- ------------------------------------------------------------------------------
-- INSERTION DES PERMISSIONS (Dictionnaire Central)
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `module`) VALUES
('dashboard.view', 'Voir le tableau de bord', 'dashboard'),
('clients.view', 'Voir les clients', 'clients'),
('clients.create', 'Créer un client', 'clients'),
('clients.edit', 'Modifier un client', 'clients'),
('clients.delete', 'Supprimer un client', 'clients'),
('fournisseurs.view', 'Voir les fournisseurs', 'fournisseurs'),
('fournisseurs.create', 'Créer un fournisseur', 'fournisseurs'),
('fournisseurs.edit', 'Modifier un fournisseur', 'fournisseurs'),
('fournisseurs.delete', 'Supprimer un fournisseur', 'fournisseurs'),
('produits.view', 'Voir les produits', 'produits'),
('produits.create', 'Créer un produit', 'produits'),
('produits.edit', 'Modifier un produit', 'produits'),
('produits.delete', 'Supprimer un produit', 'produits'),
('devis.view', 'Voir les devis', 'ventes'),
('devis.create', 'Créer un devis', 'ventes'),
('devis.edit', 'Modifier un devis', 'ventes'),
('devis.delete', 'Supprimer un devis', 'ventes'),
('devis.transform', 'Transformer un devis en facture', 'ventes'),
('factures.view', 'Voir les factures', 'ventes'),
('factures.create', 'Créer une facture', 'ventes'),
('factures.edit', 'Modifier une facture', 'ventes'),
('factures.delete', 'Supprimer une facture', 'ventes'),
('factures.import', 'Importer des lignes de facture', 'ventes'),
('commandes.view', 'Voir les commandes', 'achats'),
('commandes.create', 'Créer une commande', 'achats'),
('commandes.edit', 'Modifier une commande', 'achats'),
('commandes.delete', 'Supprimer une commande', 'achats'),
('stock.view', 'Voir le stock', 'stock'),
('stock.mouvement', 'Effectuer un mouvement de stock', 'stock'),
('stock.inventaire', 'Gérer les inventaires', 'stock'),
('reglements.view', 'Voir les règlements', 'finances'),
('reglements.create', 'Enregistrer un règlement', 'finances'),
('depenses.view', 'Voir les dépenses', 'finances'),
('depenses.create', 'Créer une dépense', 'finances'),
('dettes.view', 'Voir les dettes fournisseur', 'finances'),
('projets.view', 'Voir les projets', 'projets'),
('projets.create', 'Créer un projet', 'projets'),
('projets.edit', 'Modifier un projet', 'projets'),
('parametrage.view', 'Voir les paramètres', 'parametrage'),
('parametrage.edit', 'Modifier les paramètres', 'parametrage'),
('users.manage', 'Gérer les utilisateurs', 'parametrage'),
('roles.manage', 'Gérer les rôles', 'parametrage'),
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
('factures-achats.view', 'Voir les factures d\'\'achat', 'achats'),
('factures-achats.create', 'Créer une facture d\'\'achat', 'achats'),
('factures-achats.edit', 'Modifier une facture d\'\'achat', 'achats'),
('avoirs-fournisseurs.view', 'Voir les avoirs fournisseurs', 'achats'),
('avoirs-fournisseurs.create', 'Créer un avoir fournisseur', 'achats'),
('reporting.view', 'Voir les rapports et analyses', 'reporting'),
('caisse.view', 'Voir la trésorerie et la caisse', 'finances');

-- ------------------------------------------------------------------------------
-- 7. TELEMETRY & LOGS D'ERREURS (SuperAdmin Telemetry)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS telemetry_error_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    status_code INT DEFAULT 500,
    message TEXT NOT NULL,
    file VARCHAR(255) NULL,
    line INT NULL,
    url VARCHAR(500) NULL,
    method VARCHAR(10) NULL,
    ip VARCHAR(45) NULL,
    trace LONGTEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_telemetry_tenant_id (tenant_id),
    KEY idx_telemetry_user_id (user_id),
    KEY idx_telemetry_status_code (status_code),
    KEY idx_telemetry_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

