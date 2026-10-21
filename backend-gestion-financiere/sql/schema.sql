-- ============================================================
-- SCHÉMA SQL CORRIGÉ - VERSION FINALE
-- Fichier: backend/sql/schema_corrected.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_financiere 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gestion_financiere;

-- Supprimer les tables existantes
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS users;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tel VARCHAR(20) DEFAULT NULL,
    role ENUM('Utilisateur', 'Administrateur') DEFAULT 'Utilisateur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL DEFAULT NULL,
    actif BOOLEAN DEFAULT 1,
    
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: transactions
-- ============================================================
CREATE TABLE transactions (
    id VARCHAR(20) PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('Entrée', 'Sortie') NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    motif TEXT NOT NULL,
    date_necessaire DATE NOT NULL,
    urgence ENUM('normale', 'importante', 'urgente') DEFAULT 'normale',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'validee', 'refusee') DEFAULT 'en_attente',
    validateur_id INT NULL DEFAULT NULL,
    date_validation TIMESTAMP NULL DEFAULT NULL,
    commentaire_validation TEXT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (validateur_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user (user_id),
    INDEX idx_statut (statut),
    INDEX idx_date (date_creation),
    INDEX idx_urgence (urgence),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: password_resets
-- ============================================================
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) UNIQUE NOT NULL,
    expiry DATETIME NOT NULL,
    used BOOLEAN DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expiry (expiry)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: logs
-- ============================================================
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_date (date_action),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- Admin (password: Admin123!)
INSERT INTO users (nom, prenom, email, username, password, tel, role) VALUES
('Admin', 'Principal', 'admin@entreprise.com', 'admin', 
 '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5Kxn.FvVpJQfK', 
 '+225 07 00 00 00 01', 'Administrateur');

-- Utilisateur (password: Admin123!)
INSERT INTO users (nom, prenom, email, username, password, tel, role) VALUES
('Koné', 'Amara', 'amara.kone@entreprise.com', 'amara.kone', 
 '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5Kxn.FvVpJQfK', 
 '+225 07 00 00 00 03', 'Utilisateur');

-- Transactions de test
INSERT INTO transactions (id, user_id, type, montant, motif, date_necessaire, urgence, statut, validateur_id, date_validation) VALUES
('TXN-000001', 2, 'Entrée', 150000.00, 'Vente de produits - Commande client #ABC123', '2025-12-25', 'normale', 'validee', 1, NOW()),
('TXN-000002', 2, 'Sortie', 75000.00, 'Achat de fournitures de bureau', '2025-12-23', 'normale', 'validee', 1, NOW()),
('TXN-000003', 2, 'Entrée', 200000.00, 'Paiement client DEF', '2025-12-28', 'importante', 'en_attente', NULL, NULL),
('TXN-000004', 2, 'Sortie', 50000.00, 'Frais de transport urgent', '2025-12-22', 'urgente', 'en_attente', NULL, NULL);

-- ============================================================
-- TRIGGER: Auto-génération ID transaction
-- ============================================================
DELIMITER //

DROP TRIGGER IF EXISTS before_transaction_insert//

CREATE TRIGGER before_transaction_insert 
BEFORE INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL OR NEW.id = '' THEN
        DECLARE next_id INT;
        SELECT COALESCE(MAX(CAST(SUBSTRING(id, 5) AS UNSIGNED)), 0) + 1 
        INTO next_id FROM transactions;
        SET NEW.id = CONCAT('TXN-', LPAD(next_id, 6, '0'));
    END IF;
END//

DELIMITER ;

SELECT 'Base de données créée avec succès!' as status;