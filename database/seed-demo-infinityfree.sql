-- Import InfinityFree : selectionnez la base dans phpMyAdmin avant import

INSERT IGNORE INTO plats (id, nom, type, description, image, regime) VALUES
(1, 'Veloute de saison', 'entree', 'Legumes du marche', 'veloute.jpg', 'vegetarien'),
(2, 'Salade gourmande', 'entree', 'Mesclun et noix', 'salade.jpg', 'vegan'),
(7, 'Foie gras mi-cuit', 'entree', 'Chutney de figues', 'foie-gras.jpg', 'classique'),
(3, 'Poulet fermier', 'plat', 'Label rouge roti', 'poulet.jpg', 'classique'),
(4, 'Risotto aux champignons', 'plat', 'Cremeux parmesan', 'risotto-champignons.jpg', 'vegetarien'),
(8, 'Saumon grille', 'plat', 'Beurre citron', 'saumon.jpg', 'classique'),
(5, 'Tiramisu maison', 'dessert', 'Recette italienne', 'tiramisu.jpg', 'classique'),
(6, 'Fruits frais', 'dessert', 'Assortiment de saison', 'fruits.jpg', 'vegan'),
(9, 'Fondant chocolat', 'dessert', 'Coeur coulant', 'fondant.jpg', 'classique');

INSERT IGNORE INTO menus (id, titre, description, theme, regime, prix, min_personnes, stock, delai_jours) VALUES
(1, 'Menu Noel', 'Repas festif de fin d annee', 'Noel', 'classique', 45.00, 10, 20, 14),
(2, 'Menu Mariage', 'Reception elegante', 'Mariage', 'classique', 55.00, 20, 15, 21),
(3, 'Menu Vegan', 'Cuisine vegetale creative', 'Classique', 'vegan', 38.00, 8, 25, 7),
(4, 'Menu Business', 'Dejeuner corporate', 'Business', 'classique', 32.00, 5, 30, 5);

DELETE FROM menu_options WHERE menu_id IN (1, 2, 3, 4);

INSERT INTO menu_options (menu_id, plat_id, type) VALUES
(1, 1, 'entree'), (1, 2, 'entree'), (1, 7, 'entree'),
(1, 3, 'plat'), (1, 4, 'plat'), (1, 8, 'plat'),
(1, 5, 'dessert'), (1, 6, 'dessert'), (1, 9, 'dessert'),
(2, 1, 'entree'), (2, 2, 'entree'), (2, 7, 'entree'),
(2, 3, 'plat'), (2, 8, 'plat'), (2, 4, 'plat'),
(2, 5, 'dessert'), (2, 9, 'dessert'), (2, 6, 'dessert'),
(3, 2, 'entree'), (3, 1, 'entree'), (3, 7, 'entree'),
(3, 4, 'plat'), (3, 8, 'plat'), (3, 3, 'plat'),
(3, 6, 'dessert'), (3, 5, 'dessert'), (3, 9, 'dessert'),
(4, 1, 'entree'), (4, 7, 'entree'), (4, 2, 'entree'),
(4, 3, 'plat'), (4, 4, 'plat'), (4, 8, 'plat'),
(4, 5, 'dessert'), (4, 6, 'dessert'), (4, 9, 'dessert');
