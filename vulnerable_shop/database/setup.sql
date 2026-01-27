-- =====================================================
-- BASE DE DONNÉES VULNÉRABLE - FINS ÉDUCATIVES UNIQUEMENT
-- =====================================================

CREATE DATABASE IF NOT EXISTS vulnerable_shop;
USE vulnerable_shop;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Stockage MD5 faible (VULNÉRABILITÉ)
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user', -- user ou admin
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des produits
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    category VARCHAR(50)
);

-- Table des commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des commentaires (pour XSS)
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT,
    content TEXT,  -- Pas de sanitization (VULNÉRABILITÉ XSS)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des messages de contact
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    subject VARCHAR(200),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- DONNÉES DE TEST
-- =====================================================

-- Utilisateurs (mots de passe en MD5 - VULNÉRABILITÉ)
-- admin:admin123 | user1:password | user2:123456 | test:motdepasse123 (EN CLAIR!)
INSERT INTO users (username, password, email, role) VALUES
('admin', '0192023a7bbd73250516f069df18b500', 'admin@shop.local', 'admin'),
('user1', '5f4dcc3b5aa765d61d8327deb882cf99', 'user1@test.com', 'user'),
('user2', 'e10adc3949ba59abbe56e057f20f883e', 'user2@test.com', 'user'),
('test', 'motdepasse123', 'test@test.com', 'user');

-- Produits
INSERT INTO products (name, description, price, stock, category) VALUES
('Laptop Pro X1', 'Ordinateur portable haute performance 16Go RAM, SSD 512Go', 1299.99, 15, 'informatique'),
('Smartphone Galaxy Z', 'Smartphone dernière génération, écran AMOLED 6.5"', 899.99, 30, 'telephonie'),
('Casque Audio BT', 'Casque Bluetooth avec réduction de bruit active', 199.99, 50, 'audio'),
('Clavier Mécanique RGB', 'Clavier gaming mécanique avec rétroéclairage RGB', 129.99, 25, 'informatique'),
('Souris Gaming Pro', 'Souris gaming 16000 DPI, 8 boutons programmables', 79.99, 40, 'informatique'),
('Tablette Tab S8', 'Tablette 11 pouces, 128Go, Wi-Fi + 4G', 649.99, 20, 'telephonie'),
('Enceinte Portable', 'Enceinte Bluetooth waterproof 20W', 89.99, 35, 'audio'),
('Webcam HD 1080p', 'Webcam Full HD avec microphone intégré', 59.99, 45, 'informatique');

-- Commandes de test
INSERT INTO orders (user_id, total, status, shipping_address) VALUES
(2, 1499.98, 'delivered', '123 Rue de Test, 75001 Paris'),
(2, 199.99, 'pending', '123 Rue de Test, 75001 Paris'),
(3, 899.99, 'shipped', '456 Avenue Demo, 69001 Lyon');

-- Commentaires
INSERT INTO comments (product_id, user_id, content) VALUES
(1, 2, 'Excellent produit, très satisfait de mon achat!'),
(1, 3, 'Rapport qualité/prix imbattable.'),
(2, 2, 'Livraison rapide, téléphone conforme à la description.');
