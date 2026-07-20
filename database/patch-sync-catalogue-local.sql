-- P0 : realigner plats + menus sur le catalogue officiel
-- Safe local / InfinityFree (UPDATE + DELETE/INSERT options)
-- Executer apres catalogue-complet.sql si besoin

-- ========== PLATS (force titres / types / encoding) ==========
UPDATE plats SET nom='Veloute de saison', type='entree', description='Legumes du marche', regime='vegetarien', allergenes='lactose' WHERE id=1;
UPDATE plats SET nom='Salade Cesar vegan', type='entree', description='Crotons, sauce tahini', regime='vegan', allergenes='sesame' WHERE id=2;
UPDATE plats SET nom='Poulet fermier', type='plat', description='Label rouge roti', regime='classique', allergenes='' WHERE id=3;
UPDATE plats SET nom='Risotto champignons', type='plat', description='Creme vegetale', regime='vegetarien', allergenes='lactose' WHERE id=4;
UPDATE plats SET nom='Tiramisu maison', type='dessert', description='Recette italienne', regime='classique', allergenes='gluten,lactoeufs' WHERE id=5;
UPDATE plats SET nom='Tarte aux pommes', type='dessert', description='Tarte fine aux pommes', regime='classique', allergenes='' WHERE id=6;
UPDATE plats SET nom='Foie gras maison', type='entree', description='Chutney figue', regime='classique', allergenes='' WHERE id=7;
UPDATE plats SET nom='Carpaccio de boeuf', type='entree', description='Parmesan, roquette', regime='classique', allergenes='lactose' WHERE id=10;
UPDATE plats SET nom='Veloute potimarron', type='entree', description='Graines toastees', regime='vegan', allergenes='' WHERE id=11;
UPDATE plats SET nom='Salade quinoa', type='entree', description='Avocat, tomates confites, vinaigrette citron', regime='sans gluten', allergenes='', image='salade-quinoa.jpg' WHERE id=12;
UPDATE plats SET nom='Brochettes poulet halal', type='entree', description='Marinade orientale, menthe fraiche', regime='halal', allergenes='', image='brochettes-poulet.jpg' WHERE id=13;
UPDATE plats SET nom='Tartare de saumon', type='entree', description='Aneth, citron vert, avocat', regime='pescetarien', allergenes='poisson', image='tartare-saumon.jpg' WHERE id=14;
UPDATE plats SET nom='Terrine de canard', type='entree', description='Confiture de figues', regime='classique', allergenes='gluten,lactose' WHERE id=15;
UPDATE plats SET nom='Gaszpacho andalou', type='entree', description='Concombre, poivron', regime='vegan', allergenes='' WHERE id=16;
UPDATE plats SET nom='Veloute de champignons', type='entree', description='Creme legere', regime='vegetarien', allergenes='lactose' WHERE id=17;
UPDATE plats SET nom='Filet de boeuf', type='plat', description='Sauce morilles, pommes sarladaises', regime='classique', allergenes='', image='filet-boeuf.jpg' WHERE id=18;
UPDATE plats SET nom='Dos de saumon', type='plat', description='Beurre citron, legumes', regime='pescetarien', allergenes='poisson' WHERE id=19;
UPDATE plats SET nom='Poulet halal roti', type='plat', description='Epices douces, jus au thym', regime='halal', allergenes='', image='poulet-halal.jpg' WHERE id=20;
UPDATE plats SET nom='Risotto aux truffes', type='plat', description='Parmesan, bouillon legumes', regime='vegetarien', allergenes='lactose' WHERE id=21;
UPDATE plats SET nom='Curry legumes coco', type='plat', description='Riz basmati, coriandre', regime='vegan', allergenes='' WHERE id=22;
UPDATE plats SET nom='Canard confit', type='plat', description='Pommes sarladaises', regime='classique', allergenes='' WHERE id=23;
UPDATE plats SET nom='Bar roti', type='plat', description='Puree de patate douce, beurre citron', regime='pescetarien', allergenes='poisson', image='bar.jpg' WHERE id=24;
UPDATE plats SET nom='Poulet basquaise', type='plat', description='Poivrons, tomates, riz parfume', regime='sans lactose', allergenes='' WHERE id=25;
UPDATE plats SET nom='Lasagnes sans gluten', type='plat', description='Bolognaise maison, bechamel', regime='sans gluten', allergenes='lactose' WHERE id=26;
UPDATE plats SET nom='Buche de Noel', type='dessert', description='Creme au beurre, praline', regime='classique', allergenes='gluten,lactoeufs' WHERE id=27;
UPDATE plats SET nom='Tiramisu maison', type='dessert', description='Mascarpone, cafe arabica', regime='classique', allergenes='gluten,lactoeufs' WHERE id=28;
UPDATE plats SET nom='Mousse chocolat vegan', type='dessert', description='Cacao grand cru, fruits rouges', regime='vegan', allergenes='' WHERE id=29;
UPDATE plats SET nom='Salade de fruits', type='dessert', description='Assortiment de saison', regime='sans lactose', allergenes='' WHERE id=30;
UPDATE plats SET nom='Fondant chocolat', type='dessert', description='Coeur coulant', regime='classique', allergenes='gluten,lactoeufs' WHERE id=31;
UPDATE plats SET nom='Tarte citron', type='dessert', description='Meringue italienne', regime='vegetarien', allergenes='gluten,lactoeufs' WHERE id=32;
UPDATE plats SET nom='Financier amande', type='dessert', description='Sans farine de ble', regime='sans gluten', allergenes='oeufs,fruits a coque' WHERE id=33;
UPDATE plats SET nom='Mini burgers enfants', type='plat', description='Steak hache, frites', regime='classique', allergenes='gluten' WHERE id=34;
UPDATE plats SET nom='Nuggets poulet', type='plat', description='Sauce ketchup, legumes', regime='classique', allergenes='gluten' WHERE id=35;
UPDATE plats SET nom='Pates tomate', type='plat', description='Fromage rape', regime='vegetarien', allergenes='gluten,lactose' WHERE id=36;
UPDATE plats SET nom='Brochettes boeuf BBQ', type='plat', description='Marinade fumee', regime='classique', allergenes='' WHERE id=37;
UPDATE plats SET nom='Saucisses merguez', type='plat', description='Semoule, ratatouille', regime='classique', allergenes='' WHERE id=38;
UPDATE plats SET nom='Poulet piri-piri', type='plat', description='Recette portugaise', regime='classique', allergenes='' WHERE id=39;
UPDATE plats SET nom='Bacalhau gratin', type='plat', description='Morue, pommes de terre', regime='pescetarien', allergenes='poisson,lactose' WHERE id=40;
UPDATE plats SET nom='Mezze mediterraneens', type='entree', description='Houmous, taboule, falafels', regime='vegan', allergenes='sesame' WHERE id=41;
UPDATE plats SET nom='Brunch sucre-sale', type='entree', description='Viennoiseries, oeufs', regime='classique', allergenes='gluten,lactoeufs' WHERE id=42;
UPDATE plats SET nom='Pancakes', type='dessert', description='Sirop d erable', regime='vegetarien', allergenes='gluten,lactoeufs' WHERE id=43;
UPDATE plats SET nom='Mini clafoutis', type='dessert', description='Cerises, sucre glace', regime='vegetarien', allergenes='gluten,lactoeufs' WHERE id=44;

-- ========== MENUS (titres + prix CDC) ==========
UPDATE menus SET titre='Menu Noel', prix=45.00, min_personnes=10, theme='Noel', regime='classique', stock=25, delai_jours=14,
  description='Repas festif de fin d annee avec options classiques, vegetariennes et vegan.' WHERE id=1;
UPDATE menus SET titre='Menu Mariage', prix=55.00, min_personnes=20, theme='Mariage', regime='premium', stock=20, delai_jours=21,
  description='Reception elegante pour celebrer votre union.' WHERE id=2;
UPDATE menus SET titre='Menu Vegan', prix=38.00, min_personnes=8, theme='Classique', regime='vegan', stock=30, delai_jours=7,
  description='Cuisine 100% vegetale creative et gourmande.' WHERE id=3;
UPDATE menus SET titre='Menu Business', prix=32.00, min_personnes=5, theme='Business', regime='classique', stock=35, delai_jours=5,
  description='Dejeuner corporate efficace et raffine.' WHERE id=4;
UPDATE menus SET titre='Menu Anniversaire', prix=42.00, min_personnes=8, theme='Anniversaire', regime='classique', stock=20, delai_jours=7,
  description='Fete conviviale avec plats festifs.' WHERE id=5;
UPDATE menus SET titre='Menu Bapteme', prix=36.00, min_personnes=15, theme='Anniversaire', regime='classique', stock=18, delai_jours=10,
  description='Buffet doux et familial.' WHERE id=6;
UPDATE menus SET titre='Menu Cocktail', prix=48.00, min_personnes=20, theme='Gastronomique', regime='premium', stock=15, delai_jours=14,
  description='Bouchees raffinees et format cocktail dinatoire.' WHERE id=7;
UPDATE menus SET titre='Menu Portugais', prix=44.00, min_personnes=12, theme='Oriental', regime='classique', stock=22, delai_jours=10,
  description='Saveurs du Portugal : piri-piri, bacalhau.' WHERE id=8;
UPDATE menus SET titre='Menu Mediterraneen', prix=40.00, min_personnes=10, theme='Oriental', regime='vegetarien', stock=25, delai_jours=7,
  description='Soleil en assiette : mezze, poissons et legumes grilles.' WHERE id=9;
UPDATE menus SET titre='Menu Prestige', prix=72.00, min_personnes=15, theme='Gastronomique', regime='premium', stock=10, delai_jours=21,
  description='Selection haut de gamme pour evenements d exception.' WHERE id=10;
UPDATE menus SET titre='Menu Enfant', prix=18.00, min_personnes=2, theme='Enfant', regime='classique', stock=40, delai_jours=5,
  description='Plats adaptes aux plus jeunes.' WHERE id=11;
UPDATE menus SET titre='Menu Barbecue', prix=35.00, min_personnes=15, theme='Anniversaire', regime='classique', stock=30, delai_jours=7,
  description='Grillades et accompagnements.' WHERE id=12;
UPDATE menus SET titre='Menu Brunch', prix=28.00, min_personnes=6, theme='Brunch', regime='classique', stock=35, delai_jours=5,
  description='Formule brunch sucre-sale.' WHERE id=13;
UPDATE menus SET titre='Menu Entreprise', prix=34.00, min_personnes=10, theme='Business', regime='classique', stock=40, delai_jours=5,
  description='Formule seminaire : dejeuner complet et service rapide.' WHERE id=14;

-- ========== OPTIONS (3 entree / 3 plat / 3 dessert) ==========
DELETE FROM menu_options WHERE menu_id BETWEEN 1 AND 14;

INSERT INTO menu_options (menu_id, plat_id, type) VALUES
(1,11,'entree'),(1,12,'entree'),(1,7,'entree'),
(1,22,'plat'),(1,26,'plat'),(1,3,'plat'),
(1,29,'dessert'),(1,33,'dessert'),(1,27,'dessert'),
(2,17,'entree'),(2,12,'entree'),(2,14,'entree'),
(2,21,'plat'),(2,26,'plat'),(2,18,'plat'),
(2,6,'dessert'),(2,33,'dessert'),(2,28,'dessert'),
(3,11,'entree'),(3,16,'entree'),(3,41,'entree'),
(3,22,'plat'),(3,4,'plat'),(3,24,'plat'),
(3,29,'dessert'),(3,30,'dessert'),(3,43,'dessert'),
(4,17,'entree'),(4,12,'entree'),(4,13,'entree'),
(4,21,'plat'),(4,25,'plat'),(4,3,'plat'),
(4,30,'dessert'),(4,33,'dessert'),(4,5,'dessert'),
(5,41,'entree'),(5,12,'entree'),(5,10,'entree'),
(5,22,'plat'),(5,26,'plat'),(5,18,'plat'),
(5,29,'dessert'),(5,33,'dessert'),(5,28,'dessert'),
(6,11,'entree'),(6,12,'entree'),(6,15,'entree'),
(6,4,'plat'),(6,25,'plat'),(6,20,'plat'),
(6,6,'dessert'),(6,33,'dessert'),(6,32,'dessert'),
(7,16,'entree'),(7,12,'entree'),(7,14,'entree'),
(7,21,'plat'),(7,26,'plat'),(7,23,'plat'),
(7,29,'dessert'),(7,33,'dessert'),(7,31,'dessert'),
(8,41,'entree'),(8,12,'entree'),(8,13,'entree'),
(8,22,'plat'),(8,26,'plat'),(8,40,'plat'),
(8,30,'dessert'),(8,33,'dessert'),(8,28,'dessert'),
(9,41,'entree'),(9,12,'entree'),(9,14,'entree'),
(9,22,'plat'),(9,25,'plat'),(9,24,'plat'),
(9,29,'dessert'),(9,33,'dessert'),(9,6,'dessert'),
(10,11,'entree'),(10,12,'entree'),(10,15,'entree'),
(10,21,'plat'),(10,26,'plat'),(10,18,'plat'),
(10,29,'dessert'),(10,33,'dessert'),(10,31,'dessert'),
(11,16,'entree'),(11,11,'entree'),(11,42,'entree'),
(11,36,'plat'),(11,35,'plat'),(11,34,'plat'),
(11,43,'dessert'),(11,44,'dessert'),(11,30,'dessert'),
(12,41,'entree'),(12,12,'entree'),(12,10,'entree'),
(12,22,'plat'),(12,25,'plat'),(12,37,'plat'),
(12,29,'dessert'),(12,33,'dessert'),(12,30,'dessert'),
(13,11,'entree'),(13,12,'entree'),(13,42,'entree'),
(13,4,'plat'),(13,25,'plat'),(13,36,'plat'),
(13,43,'dessert'),(13,33,'dessert'),(13,6,'dessert'),
(14,1,'entree'),(14,12,'entree'),(14,13,'entree'),
(14,21,'plat'),(14,26,'plat'),(14,3,'plat'),
(14,30,'dessert'),(14,33,'dessert'),(14,5,'dessert');

-- Verif
SELECT m.id, m.titre, m.prix,
  SUM(mo.type='entree') AS n_entree,
  SUM(mo.type='plat') AS n_plat,
  SUM(mo.type='dessert') AS n_dessert
FROM menus m
LEFT JOIN menu_options mo ON mo.menu_id = m.id
WHERE m.id BETWEEN 1 AND 14
GROUP BY m.id, m.titre, m.prix
ORDER BY m.id;
