-- Donnees de demo (importer apres schema-render.sql si menus vides)
USE vite_gourmand;

INSERT IGNORE INTO menus (id, titre, description, theme, regime, prix, min_personnes, stock, delai_jours) VALUES
(1, 'Menu Noel', 'Repas festif de fin d annee', 'Noel', 'classique', 45.00, 10, 20, 14),
(2, 'Menu Mariage', 'Reception elegante', 'Mariage', 'classique', 55.00, 20, 15, 21),
(3, 'Menu Vegan', 'Cuisine vegetale creative', 'Classique', 'vegan', 38.00, 8, 25, 7),
(4, 'Menu Business', 'Dejeuner corporate', 'Business', 'classique', 32.00, 5, 30, 5);

INSERT IGNORE INTO plats (id, nom, type, description, image, regime) VALUES
(1, 'Veloute de saison', 'entree', 'Legumes du marche', 'veloute.jpg', 'vegetarien'),
(2, 'Salade gourmande', 'entree', 'Mesclun et noix', 'salade.jpg', 'vegan'),
(3, 'Poulet fermier', 'plat', 'Label rouge', 'poulet.jpg', 'classique'),
(4, 'Risotto aux champignons', 'plat', 'Cremeux', 'risotto.jpg', 'vegetarien'),
(5, 'Tiramisu maison', 'dessert', 'Recette italienne', 'tiramisu.jpg', 'classique'),
(6, 'Fruits frais', 'dessert', 'Assortiment', 'fruits.jpg', 'vegan');

INSERT IGNORE INTO menu_options (menu_id, plat_id, type) VALUES
(1, 1, 'entree'), (1, 3, 'plat'), (1, 5, 'dessert'),
(2, 1, 'entree'), (2, 3, 'plat'), (2, 5, 'dessert'),
(3, 2, 'entree'), (3, 4, 'plat'), (3, 6, 'dessert'),
(4, 1, 'entree'), (4, 3, 'plat'), (4, 5, 'dessert');
