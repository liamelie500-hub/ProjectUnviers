-- =============================================
-- BASE DE DONNÉES UNIVERS
-- =============================================

-- Supprimer la base si elle existe déjà
DROP DATABASE IF EXISTS univers;

-- Créer la base de données
CREATE DATABASE univers;
USE univers;

-- =============================================
-- TABLE DES UTILISATEURS
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE DES FICHIERS
-- =============================================
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    full_description TEXT,
    filename VARCHAR(255) NOT NULL,
    version VARCHAR(50),
    size VARCHAR(50),
    image VARCHAR(255),
    category VARCHAR(50),
    changelog TEXT,
    downloads INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    uploaded_by INT,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE DES ANNONCES
-- =============================================
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE DES LOGS
-- =============================================
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    details TEXT,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE DES SESSIONS (optionnelle)
-- =============================================
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DONNÉES DE DÉMONSTRATION
-- =============================================

-- 1. Créer un utilisateur administrateur
-- Mot de passe: admin123 (hashé avec argon2id)
INSERT INTO users (username, email, password, is_admin, is_active) VALUES 
('admin', 'admin@univers.com', '$2y$10$XQ1QYVQrYVQrYVQrYVQrYuVQrYVQrYVQrYVQrYVQrYVQrYVQrYVQ', TRUE, TRUE);

-- 2. Créer quelques utilisateurs de démonstration
INSERT INTO users (username, email, password, is_admin, is_active) VALUES 
('johndoe', 'john@email.com', '$2y$10$XQ1QYVQrYVQrYVQrYVQrYuVQrYVQrYVQrYVQrYVQrYVQrYVQrYVQ', FALSE, TRUE),
('janedoe', 'jane@email.com', '$2y$10$XQ1QYVQrYVQrYVQrYVQrYuVQrYVQrYVQrYVQrYVQrYVQrYVQrYVQ', FALSE, TRUE),
('bobsmith', 'bob@email.com', '$2y$10$XQ1QYVQrYVQrYVQrYVQrYuVQrYVQrYVQrYVQrYVQrYVQrYVQrYVQ', FALSE, TRUE);

-- 3. Ajouter des fichiers de démonstration
INSERT INTO files (name, description, full_description, filename, version, size, category, changelog, downloads, uploaded_by) VALUES
('Build', 'Logiciel professionnel de gestion d\'entreprise', 'Logiciel complet pour la gestion de projets, clients et factures. Interface intuitive et puissante.', 'logiciel-pro-max.zip', '3.2.0', '450 MB', 'logiciels', 'Nouvelle interface utilisateur\nCorrection de bugs critiques\nAjout du module de reporting', 156, 1),

('luncher', 'Jeu d\'aventure en monde ouvert avec graphismes HD', 'Explorez un monde immense rempli de quêtes, de monstres et de trésors. Graphismes 4K et gameplay immersif.', 'jeu-aventure-extreme.exe', '2.1.0', '2.5 GB', 'jeux', 'Nouveaux mondes à explorer\nAmélioration des graphismes\nCorrection des bugs de performance', 432, 1),

('🚀 Nouvelle version 3.0 disponible !', 'Nous sommes ravis d\'annoncer la sortie de la version 3.0 de notre application. Cette mise à jour majeure apporte de nombreuses améliorations et nouvelles fonctionnalités. N\'hésitez pas à la télécharger dès maintenant !', TRUE),

('📢 Maintenance planifiée', 'Le site sera en maintenance le dimanche 25 juillet de 2h à 4h du matin pour une mise à jour du serveur. Nous vous remercions de votre compréhension.', TRUE),

('🎯 Nouveau concours', 'Participez à notre nouveau concours et gagnez des cadeaux exceptionnels ! Pour plus d\'informations, consultez la page dédiée. Bonne chance à tous !', TRUE);

-- =============================================
-- VÉRIFICATION DES DONNÉES
-- =============================================

-- Voir les utilisateurs
SELECT * FROM users;

-- Voir les fichiers
SELECT * FROM files;

-- Voir les annonces
SELECT * FROM announcements;

-- Statistiques
SELECT 
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM files) AS total_files,
    (SELECT COUNT(*) FROM announcements) AS total_announcements;