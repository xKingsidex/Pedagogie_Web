-- Mise à jour des images produits (à exécuter si setup.sql a déjà été joué)
USE vulnerable_shop;

UPDATE products SET image = 'laptop.jpg'     WHERE name = 'Laptop Pro X1';
UPDATE products SET image = 'smartphone.jpg' WHERE name = 'Smartphone Galaxy';
UPDATE products SET image = 'casque.png'     WHERE name = 'Casque Audio BT';
UPDATE products SET image = 'clavier.jpg'    WHERE name = 'Clavier Mécanique';
UPDATE products SET image = 'souris.jpg'     WHERE name = 'Souris Gaming';
UPDATE products SET image = 'tablette.jpg'   WHERE name = 'Tablette Tab S8';
UPDATE products SET image = 'enceinte.jpg'   WHERE name = 'Enceinte Portable';
UPDATE products SET image = 'webcam.jpg'     WHERE name = 'Webcam HD';

-- Vérification
SELECT id, name, image FROM products;
