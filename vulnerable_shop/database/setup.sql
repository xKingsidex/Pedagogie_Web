-- =====================================================
-- BASE DE DONNÉES VULNÉRABLE - FINS ÉDUCATIVES UNIQUEMENT
-- Supprimer et recréer la base complète
-- =====================================================

DROP DATABASE IF EXISTS vulnerable_shop;
CREATE DATABASE vulnerable_shop;
USE vulnerable_shop;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des produits
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    category VARCHAR(50)
);

-- Table des commandes
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des commentaires
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- UTILISATEURS
-- admin:admin123 (hash MD5)
-- user1:password (hash MD5)
-- test:motdepasse123 (EN CLAIR - visible avec injection UNION!)
-- =====================================================

INSERT INTO users (username, password, email, role) VALUES
('admin', '0192023a7bbd73250516f069df18b500', 'admin@techstore.com', 'admin'),
('user1', '5f4dcc3b5aa765d61d8327deb882cf99', 'user1@email.com', 'user'),
('test', 'motdepasse123', 'test@email.com', 'user');

-- =====================================================
-- PRODUITS
-- =====================================================

INSERT INTO products (name, description, price, stock, image, category) VALUES
('Laptop Pro X1', 'Ordinateur portable haute performance', 1299.99, 15, 'laptop.jpg', 'informatique'),
('Smartphone Galaxy', 'Smartphone dernière génération', 899.99, 30, 'smartphone.jpg', 'telephonie'),
('Casque Audio BT', 'Casque Bluetooth réduction de bruit', 199.99, 50, 'casque.png', 'audio'),
('Clavier Mécanique', 'Clavier gaming RGB', 129.99, 25, 'clavier.jpg', 'informatique'),
('Souris Gaming', 'Souris gaming 16000 DPI', 79.99, 40, 'souris.jpg', 'informatique'),
('Tablette Tab S8', 'Tablette 11 pouces 128Go', 649.99, 20, 'tablette.jpg', 'telephonie'),
('Enceinte Portable', 'Enceinte Bluetooth 20W', 89.99, 35, 'enceinte.jpg', 'audio'),
('Webcam HD', 'Webcam 1080p avec micro', 59.99, 45, 'webcam.jpg', 'informatique');

-- =====================================================
-- COMMANDES
-- =====================================================

INSERT INTO orders (user_id, total, status, shipping_address) VALUES
(1, 1299.99, 'delivered', '1 Rue Admin, 75001 Paris - CODE PORTE: 1234'),
(2, 899.99, 'shipped', '15 Avenue User, 69001 Lyon - DIGICODE: 5678'),
(3, 199.99, 'pending', '42 Boulevard Test, 13001 Marseille');

-- =====================================================
-- COMMENTAIRES
-- =====================================================

INSERT INTO comments (product_id, user_id, content) VALUES
(1, 2, 'Excellent produit!'),
(2, 3, 'Très satisfait.');
