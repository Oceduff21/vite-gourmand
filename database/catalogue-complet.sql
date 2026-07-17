-- Catalogue complet Vite & Gourmand
-- 1) Executer migration-v2-infinityfree.sql
-- 2) Importer ce fichier dans phpMyAdmin (base selectionnee)

-- ========== PLATS SUPPLEMENTAIRES ==========
INSERT IGNORE INTO plats (id, nom, type, description, image, regime, allergenes) VALUES
(10, 'Carpaccio de boeuf', 'entree', 'Parmesan, roquette, huile de truffe', 'carpaccio-boeuf.jpg', 'classique', 'lactose'),
(11, 'Veloute potimarron', 'entree', 'Graines toastees, huile de noisette', 'veloute-potimarron.jpg', 'vegan', ''),
(12, 'Salade quinoa', 'entree', 'Avocat, tomates confites, vinaigrette citron', 'salade-quinoa.jpg', 'sans gluten', ''),
(13, 'Brochettes poulet halal', 'entree', 'Marinade orientale, menthe fraiche', 'brochettes-poulet.jpg', 'halal', ''),
(14, 'Tartare de saumon', 'entree', 'Aneth, citron vert, avocat', 'tartare-saumon.jpg', 'pescetarien', 'poisson'),
(15, 'Terrine de canard', 'entree', 'Confiture de figues, pain brioche', 'terrine-canard.jpg', 'classique', 'gluten,lactose'),
(16, 'Gaszpacho andalou', 'entree', 'Concombre, poivron, croutons (sans)', 'gazpacho.jpg', 'vegan', ''),
(17, 'Veloute de champignons', 'entree', 'Creme legere, persil', 'veloute-champignons.jpg', 'vegetarien', 'lactose'),
(18, 'Filet de boeuf', 'plat', 'Sauce morilles, pommes sarladaises', 'filet-boeuf.jpg', 'classique', ''),
(19, 'Dos de saumon', 'plat', 'Beurre citron, legumes de saison', 'saumon.jpg', 'pescetarien', 'poisson'),
(20, 'Poulet halal roti', 'plat', 'Epices douces, jus au thym', 'poulet-halal.jpg', 'halal', ''),
(21, 'Risotto aux truffes', 'plat', 'Parmesan, bouillon legumes', 'risotto-truffes.jpg', 'vegetarien', 'lactose'),
(22, 'Curry legumes coco', 'plat', 'Riz basmati, coriandre', 'curry.jpg', 'vegan', ''),
(23, 'Canard confit', 'plat', 'Pommes sarladaises, jus reduit', 'canard.jpg', 'classique', ''),
(24, 'Bar rôti', 'plat', 'Puree de patate douce, beurre citron', 'bar.jpg', 'pescetarien', 'poisson'),
(25, 'Poulet basquaise', 'plat', 'Poivrons, tomates, riz parfume', 'poulet-basquaise.jpg', 'sans lactose', ''),
(26, 'Lasagnes sans gluten', 'plat', 'Bolognaise maison, bechamel', 'lasagne.jpg', 'sans gluten', 'lactose'),
(27, 'Buche de Noel', 'dessert', 'Creme au beurre, praline noisette', 'buche.jpg', 'classique', 'gluten,lactoeufs'),
(28, 'Tiramisu maison', 'dessert', 'Mascarpone, cafe arabica', 'tiramisu.jpg', 'classique', 'gluten,lactoeufs'),
(29, 'Mousse chocolat vegan', 'dessert', 'Cacao grand cru, fruits rouges', 'mousse-chocolat.jpg', 'vegan', ''),
(30, 'Salade de fruits', 'dessert', 'Assortiment de saison', 'fruits.jpg', 'sans lactose', ''),
(31, 'Fondant chocolat', 'dessert', 'Coeur coulant, glace vanille', 'fondant.jpg', 'classique', 'gluten,lactoeufs'),
(32, 'Tarte citron', 'dessert', 'Meringue italienne', 'tarte-citron.jpg', 'vegetarien', 'gluten,lactoeufs'),
(33, 'Financier amande', 'dessert', 'Sans farine de ble', 'financier.jpg', 'sans gluten', 'oeufs,fruits a coque'),
(34, 'Mini burgers enfants', 'plat', 'Steak hache, frites maison', 'burger-enfant.jpg', 'classique', 'gluten'),
(35, 'Nuggets poulet', 'plat', 'Sauce ketchup, legumes', 'nuggets.jpg', 'classique', 'gluten'),
(36, 'Pates tomate', 'plat', 'Fromage rape, sans viande', 'pates.jpg', 'vegetarien', 'gluten,lactose'),
(37, 'Brochettes boeuf BBQ', 'plat', 'Marinade fumee, salade verte', 'boeuf.jpg', 'classique', ''),
(38, 'Saucisses merguez', 'plat', 'Semoule, ratatouille', 'merguez.jpg', 'classique', ''),
(39, 'Poulet piri-piri', 'plat', 'Recette portugaise authentique', 'piri-piri.jpg', 'classique', ''),
(40, 'Bacalhau gratin', 'plat', 'Morue, pommes de terre, creme', 'bacalhau.jpg', 'pescetarien', 'poisson,lactose'),
(41, 'Mezze mediterraneens', 'entree', 'Houmous, taboule, falafels', 'mezze.jpg', 'vegan', 'sesame'),
(42, 'Brunch sucre-sale', 'entree', 'Viennoiseries, oeufs brouilles', 'brunch-plateau.jpg', 'classique', 'gluten,lactoeufs'),
(43, 'Pancakes', 'dessert', 'Sirop d erable, fruits frais', 'pancakes.jpg', 'vegetarien', 'gluten,lactoeufs'),
(44, 'Mini clafoutis', 'dessert', 'Cerises, sucre glace', 'clafoutis.jpg', 'vegetarien', 'gluten,lactoeufs');

-- ========== MISE A JOUR MENUS EXISTANTS ==========
UPDATE menus SET
  description = 'Repas festif de fin d annee avec options classiques, vegetariennes et vegan pour chaque service.',
  theme = 'Noel', regime = 'classique', conditions = 'Commande minimum 10 personnes. Livraison 14 jours avant.',
  stock = 25, delai_jours = 14
WHERE id = 1;

UPDATE menus SET
  description = 'Reception elegante pour célébrer votre union. Service raffine et personnalisation par invite.',
  theme = 'Mariage', regime = 'premium', conditions = 'Devis sur mesure. Service en salle disponible.',
  stock = 20, delai_jours = 21
WHERE id = 2;

UPDATE menus SET
  description = 'Cuisine 100% vegetale creative, gourmande et colorée pour tous vos événements.',
  theme = 'Classique', regime = 'vegan', stock = 30, delai_jours = 7
WHERE id = 3;

UPDATE menus SET
  description = 'Dejeuner corporate efficace et raffine pour vos reunions et seminaires.',
  theme = 'Business', regime = 'classique', stock = 35, delai_jours = 5
WHERE id = 4;

-- ========== NOUVEAUX MENUS ==========
INSERT IGNORE INTO menus (id, titre, description, theme, regime, prix, min_personnes, stock, delai_jours, conditions) VALUES
(5, 'Menu Anniversaire', 'Fête conviviale avec plats festifs et options pour tous les régimes.', 'Anniversaire', 'classique', 42.00, 8, 20, 7, 'Deco de table en option.'),
(6, 'Menu Bapteme', 'Buffet doux et familial pour célébrer en douceur.', 'Anniversaire', 'classique', 36.00, 15, 18, 10, 'Minimum 15 personnes.'),
(7, 'Menu Cocktail', 'Bouchées raffinées et format cocktail dinatoire.', 'Gastronomique', 'premium', 48.00, 20, 15, 14, 'Service debout recommande.'),
(8, 'Menu Portugais', 'Saveurs du Portugal : piri-piri, bacalhau et douceurs maison.', 'Oriental', 'classique', 44.00, 12, 22, 10, ''),
(9, 'Menu Mediterraneen', 'Soleil en assiette : mezze, poissons et legumes grilles.', 'Oriental', 'vegetarien', 40.00, 10, 25, 7, ''),
(10, 'Menu Prestige', 'Notre sélection haut de gamme pour événements d exception.', 'Gastronomique', 'premium', 72.00, 15, 10, 21, 'Sommelier sur demande.'),
(11, 'Menu Enfant', 'Plats adaptes aux plus jeunes, equilibres et gourmands.', 'Enfant', 'classique', 18.00, 6, 40, 5, 'A partir de 3 ans.'),
(12, 'Menu Barbecue', 'Grillades et accompagnements pour vos fetes en plein air.', 'Anniversaire', 'classique', 35.00, 15, 30, 7, 'Location barbecue disponible.'),
(13, 'Menu Brunch', 'Formule brunch sucre-sale pour vos matinees evenementielles.', 'Brunch', 'classique', 28.00, 6, 35, 5, 'Service 10h-14h.'),
(14, 'Menu Entreprise', 'Formule seminaire : dejeuner complet et service rapide.', 'Business', 'classique', 34.00, 10, 40, 5, 'Facturation entreprise.');

-- ========== OPTIONS PAR MENU (3 entree / 3 plat / 3 dessert) ==========
DELETE FROM menu_options WHERE menu_id BETWEEN 1 AND 14;

INSERT INTO menu_options (menu_id, plat_id, type) VALUES
-- Menu 1 Noel
(1,7,'entree'),(1,11,'entree'),(1,17,'entree'),
(1,3,'plat'),(1,21,'plat'),(1,22,'plat'),
(1,5,'dessert'),(1,27,'dessert'),(1,29,'dessert'),
-- Menu 2 Mariage
(2,7,'entree'),(2,15,'entree'),(2,14,'entree'),
(2,18,'plat'),(2,19,'plat'),(2,23,'plat'),
(2,28,'dessert'),(2,31,'dessert'),(2,32,'dessert'),
-- Menu 3 Vegan
(3,2,'entree'),(3,11,'entree'),(3,16,'entree'),
(3,4,'plat'),(3,22,'plat'),(3,21,'plat'),
(3,6,'dessert'),(3,29,'dessert'),(3,30,'dessert'),
-- Menu 4 Business
(4,1,'entree'),(4,12,'entree'),(4,17,'entree'),
(4,3,'plat'),(4,25,'plat'),(4,26,'plat'),
(4,5,'dessert'),(4,30,'dessert'),(4,33,'dessert'),
-- Menu 5 Anniversaire
(5,10,'entree'),(5,17,'entree'),(5,41,'entree'),
(5,18,'plat'),(5,21,'plat'),(5,22,'plat'),
(5,28,'dessert'),(5,29,'dessert'),(5,43,'dessert'),
-- Menu 6 Bapteme
(6,1,'entree'),(6,11,'entree'),(6,12,'entree'),
(6,20,'plat'),(6,25,'plat'),(6,4,'plat'),
(6,30,'dessert'),(6,32,'dessert'),(6,6,'dessert'),
-- Menu 7 Cocktail
(7,15,'entree'),(7,14,'entree'),(7,10,'entree'),
(7,23,'plat'),(7,19,'plat'),(7,18,'plat'),
(7,31,'dessert'),(7,28,'dessert'),(7,33,'dessert'),
-- Menu 8 Portugais
(8,13,'entree'),(8,15,'entree'),(8,41,'entree'),
(8,39,'plat'),(8,40,'plat'),(8,19,'plat'),
(8,28,'dessert'),(8,30,'dessert'),(8,32,'dessert'),
-- Menu 9 Mediterraneen
(9,41,'entree'),(9,16,'entree'),(9,2,'entree'),
(9,24,'plat'),(9,22,'plat'),(9,21,'plat'),
(9,6,'dessert'),(9,30,'dessert'),(9,29,'dessert'),
-- Menu 10 Prestige
(10,7,'entree'),(10,14,'entree'),(10,15,'entree'),
(10,18,'plat'),(10,23,'plat'),(10,19,'plat'),
(10,27,'dessert'),(10,31,'dessert'),(10,28,'dessert'),
-- Menu 11 Enfant
(11,42,'entree'),(11,1,'entree'),(11,16,'entree'),
(11,34,'plat'),(11,35,'plat'),(11,36,'plat'),
(11,43,'dessert'),(11,30,'dessert'),(11,44,'dessert'),
-- Menu 12 Barbecue
(12,10,'entree'),(12,13,'entree'),(12,41,'entree'),
(12,37,'plat'),(12,38,'plat'),(12,25,'plat'),
(12,30,'dessert'),(12,29,'dessert'),(12,28,'dessert'),
-- Menu 13 Brunch
(13,42,'entree'),(13,17,'entree'),(13,11,'entree'),
(13,36,'plat'),(13,4,'plat'),(13,25,'plat'),
(13,43,'dessert'),(13,44,'dessert'),(13,6,'dessert'),
-- Menu 14 Entreprise
(14,1,'entree'),(14,12,'entree'),(14,13,'entree'),
(14,3,'plat'),(14,20,'plat'),(14,26,'plat'),
(14,5,'dessert'),(14,33,'dessert'),(14,30,'dessert');

-- ========== BOISSONS ==========
INSERT IGNORE INTO boissons (id, nom, prix) VALUES
(1, 'Coca-Cola 33cl', 2.50),
(2, 'Orangina 33cl', 2.50),
(3, 'Eau minerale 50cl', 1.80),
(4, 'Eau petillante 75cl', 3.00),
(5, 'Jus d orange frais', 3.50),
(6, 'Jus de pomme', 3.00),
(7, 'Virgin Mojito', 4.50),
(8, 'Limonade artisanale', 3.20),
(9, 'Biere blonde 33cl', 4.00),
(10, 'Biere brune 33cl', 4.20),
(11, 'Vin rouge Bordeaux AOC', 18.00),
(12, 'Vin rouge Medoc', 22.00),
(13, 'Vin blanc Entre-deux-Mers', 16.00),
(14, 'Vin blanc Sancerre', 24.00),
(15, 'Vin rose de Provence', 17.00),
(16, 'Champagne Brut', 45.00),
(17, 'Champagne Rose', 52.00),
(18, 'Whisky 4cl', 6.00),
(19, 'Rhum 4cl', 5.50),
(20, 'Cafe expresso', 2.00);

UPDATE boissons SET categorie = 'soft', description = 'Boisson gazeuse' WHERE id IN (1,2,8);
UPDATE boissons SET categorie = 'eau', description = 'Bouteille individuelle' WHERE id IN (3,4);
UPDATE boissons SET categorie = 'jus', description = 'Presse ou artisanal' WHERE id IN (5,6);
UPDATE boissons SET categorie = 'cocktail_sans_alcool' WHERE id = 7;
UPDATE boissons SET categorie = 'biere' WHERE id IN (9,10);
UPDATE boissons SET categorie = 'vin_rouge' WHERE id IN (11,12);
UPDATE boissons SET categorie = 'vin_blanc' WHERE id IN (13,14);
UPDATE boissons SET categorie = 'vin_rose' WHERE id = 15;
UPDATE boissons SET categorie = 'champagne' WHERE id IN (16,17);
UPDATE boissons SET categorie = 'spiritueux' WHERE id IN (18,19);
UPDATE boissons SET categorie = 'autre', description = 'Pause cafe' WHERE id = 20;

-- ========== BOISSONS PAR MENU (formule de base) ==========
DELETE FROM menu_boissons WHERE menu_id BETWEEN 1 AND 14;

INSERT INTO menu_boissons (menu_id, boisson_id) VALUES
(1,3),(1,5),(1,11),(1,13),(1,16),
(2,3),(2,4),(2,5),(2,11),(2,12),(2,15),(2,16),(2,17),
(3,3),(3,5),(3,6),(3,7),(3,8),
(4,3),(4,5),(4,20),
(5,1),(5,2),(5,3),(5,5),(5,9),
(6,3),(6,5),(6,6),(6,8),
(7,7),(7,9),(7,11),(7,15),(7,16),(7,18),
(8,3),(8,9),(8,11),(8,13),
(9,3),(9,5),(9,13),(9,15),
(10,4),(10,11),(10,12),(10,14),(10,16),(10,17),(10,18),
(11,1),(11,2),(11,3),(11,5),
(12,1),(12,2),(12,3),(12,9),(12,10),
(13,5),(13,6),(13,7),(13,8),(13,20),
(14,3),(14,5),(14,13),(14,20);
