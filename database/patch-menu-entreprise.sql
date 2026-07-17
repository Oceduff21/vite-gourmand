-- Corriger Menu Entreprise (id 14) : 3 entrees + 3 plats + 3 desserts
-- Cause frequente de "2 entrees seulement" : plats 12 ou 13 absents en BDD
-- Executer dans phpMyAdmin

-- S assurer que les plats du menu existent (INSERT IGNORE si manquants)
INSERT IGNORE INTO plats (id, nom, type, description, image, regime, allergenes) VALUES
(1, 'Veloute de saison', 'entree', 'Legumes du marche', 'veloute.jpg', 'vegetarien', 'lactose'),
(3, 'Poulet fermier', 'plat', 'Label rouge roti', 'poulet.jpg', 'classique', ''),
(5, 'Tiramisu maison', 'dessert', 'Recette italienne', 'tiramisu.jpg', 'classique', 'gluten,lactoeufs'),
(12, 'Salade quinoa', 'entree', 'Avocat, tomates confites, vinaigrette citron', 'salade-quinoa.jpg', 'sans gluten', ''),
(13, 'Brochettes poulet halal', 'entree', 'Marinade orientale, menthe fraiche', 'brochettes-poulet.jpg', 'halal', ''),
(20, 'Poulet halal roti', 'plat', 'Epices douces, jus au thym', 'poulet-halal.jpg', 'halal', ''),
(26, 'Lasagnes sans gluten', 'plat', 'Bolognaise maison, bechamel', 'lasagne.jpg', 'sans gluten', 'lactose'),
(30, 'Salade de fruits', 'dessert', 'Assortiment de saison', 'fruits.jpg', 'sans lactose', ''),
(33, 'Financier amande', 'dessert', 'Sans farine de ble', 'financier.jpg', 'sans gluten', 'oeufs,fruits a coque');

-- S assurer que le menu Entreprise existe
INSERT IGNORE INTO menus (id, titre, description, theme, regime, prix, min_personnes, stock, delai_jours, conditions) VALUES
(14, 'Menu Entreprise', 'Formule seminaire : dejeuner complet et service rapide.', 'Business', 'classique', 34.00, 10, 40, 5, 'Facturation entreprise.');

UPDATE menus SET
  titre = 'Menu Entreprise',
  description = 'Formule seminaire : dejeuner complet et service rapide.',
  theme = 'Business',
  regime = 'classique',
  prix = 34.00,
  min_personnes = 10,
  stock = 40,
  delai_jours = 5,
  image_principale = COALESCE(NULLIF(image_principale, ''), 'menu-entreprise.jpg')
WHERE id = 14;

-- Reconstruire les 9 options (3 entree / 3 plat / 3 dessert)
DELETE FROM menu_options WHERE menu_id = 14;

INSERT INTO menu_options (menu_id, plat_id, type) VALUES
(14, 1,  'entree'),
(14, 12, 'entree'),
(14, 13, 'entree'),
(14, 3,  'plat'),
(14, 20, 'plat'),
(14, 26, 'plat'),
(14, 5,  'dessert'),
(14, 33, 'dessert'),
(14, 30, 'dessert');

-- Verification (doit afficher 3 lignes entree) :
-- SELECT mo.type, p.id, p.nom, p.image
-- FROM menu_options mo
-- JOIN plats p ON p.id = mo.plat_id
-- WHERE mo.menu_id = 14
-- ORDER BY FIELD(mo.type, 'entree', 'plat', 'dessert'), p.nom;
