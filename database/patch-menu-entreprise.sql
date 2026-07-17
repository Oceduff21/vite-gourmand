-- FIX Menu Entreprise : 3 entrees visibles sur le site
-- Cause : patch-regimes-boissons.sql utilisait plat id 7 (foie gras) ou 17
--         souvent ABSENTS en prod → seulement 2 entrees apres JOIN SQL.
--
-- Executer TOUT ce script dans phpMyAdmin (une seule fois).

-- ========== 1. DIAGNOSTIC (lire le resultat avant/apres) ==========
SELECT 'AVANT' AS etape, m.id AS menu_id, m.titre, mo.type, mo.plat_id, p.nom AS plat_nom
FROM menus m
LEFT JOIN menu_options mo ON mo.menu_id = m.id AND mo.type = 'entree'
LEFT JOIN plats p ON p.id = mo.plat_id
WHERE LOWER(m.titre) LIKE '%entreprise%'
ORDER BY mo.plat_id;

-- ========== 2. CREER LES PLATS MANQUANTS (plat id 1 surtout) ==========
-- INSERT IGNORE ne corrige pas un plat deja present avec un mauvais type.
INSERT INTO plats (id, nom, type, description, image, regime, allergenes) VALUES
(1,  'Veloute de saison', 'entree', 'Legumes du marche', 'veloute.jpg', 'vegetarien', 'lactose'),
(3,  'Poulet fermier', 'plat', 'Label rouge roti', 'poulet.jpg', 'classique', ''),
(5,  'Tiramisu maison', 'dessert', 'Recette italienne', 'tiramisu.jpg', 'classique', 'gluten,lactoeufs'),
(7,  'Foie gras mi-cuit', 'entree', 'Chutney de figues', 'foie-gras.jpg', 'classique', 'lactose'),
(12, 'Salade quinoa', 'entree', 'Avocat, tomates confites, vinaigrette citron', 'salade-quinoa.jpg', 'sans gluten', ''),
(13, 'Brochettes poulet halal', 'entree', 'Marinade orientale, menthe fraiche', 'brochettes-poulet.jpg', 'halal', ''),
(17, 'Veloute de champignons', 'entree', 'Creme legere, persil', 'veloute-champignons.jpg', 'vegetarien', 'lactose'),
(20, 'Poulet halal roti', 'plat', 'Epices douces, jus au thym', 'poulet-halal.jpg', 'halal', ''),
(21, 'Risotto aux truffes', 'plat', 'Parmesan, bouillon legumes', 'risotto-truffes.jpg', 'vegetarien', 'lactose'),
(26, 'Lasagnes sans gluten', 'plat', 'Bolognaise maison, bechamel', 'lasagne.jpg', 'sans gluten', 'lactose'),
(30, 'Salade de fruits', 'dessert', 'Assortiment de saison', 'fruits.jpg', 'sans lactose', ''),
(33, 'Financier amande', 'dessert', 'Sans farine de ble', 'financier.jpg', 'sans gluten', 'oeufs,fruits a coque')
ON DUPLICATE KEY UPDATE
  nom = VALUES(nom),
  type = VALUES(type),
  description = VALUES(description),
  image = VALUES(image),
  regime = VALUES(regime),
  allergenes = VALUES(allergenes);

-- Corriger le type si un plat etait mal enregistre
UPDATE plats SET type = 'entree' WHERE id IN (1, 7, 12, 13, 17);
UPDATE plats SET type = 'plat'   WHERE id IN (3, 20, 21, 26);
UPDATE plats SET type = 'dessert' WHERE id IN (5, 30, 33);

-- ========== 3. MENU ENTREPRISE (id 14 ou titre) ==========
INSERT IGNORE INTO menus (id, titre, description, theme, regime, prix, min_personnes, stock, delai_jours, conditions, image_principale) VALUES
(14, 'Menu Entreprise', 'Formule seminaire : dejeuner complet et service rapide.', 'Business', 'classique', 34.00, 10, 40, 5, 'Facturation entreprise.', 'menu-entreprise.jpg');

UPDATE menus SET
  titre = 'Menu Entreprise',
  theme = 'Business',
  image_principale = 'menu-entreprise.jpg'
WHERE LOWER(titre) LIKE '%entreprise%';

-- Cibler le bon menu (id 14 ou titre Entreprise)
SET @menu_entreprise = (SELECT id FROM menus WHERE LOWER(titre) LIKE '%entreprise%' ORDER BY id LIMIT 1);

DELETE FROM menu_options WHERE menu_id = @menu_entreprise;

INSERT INTO menu_options (menu_id, plat_id, type) VALUES
(@menu_entreprise, 1,  'entree'),
(@menu_entreprise, 12, 'entree'),
(@menu_entreprise, 13, 'entree'),
(@menu_entreprise, 3,  'plat'),
(@menu_entreprise, 20, 'plat'),
(@menu_entreprise, 26, 'plat'),
(@menu_entreprise, 5,  'dessert'),
(@menu_entreprise, 33, 'dessert'),
(@menu_entreprise, 30, 'dessert');

-- ========== 4. VERIFICATION (doit afficher 3 lignes) ==========
SELECT 'APRES' AS etape, m.id AS menu_id, m.titre, mo.type, mo.plat_id, p.nom AS plat_nom
FROM menus m
JOIN menu_options mo ON mo.menu_id = m.id AND mo.type = 'entree'
JOIN plats p ON p.id = mo.plat_id
WHERE m.id = @menu_entreprise
ORDER BY mo.plat_id;

-- Compter les entrees (doit = 3) :
SELECT COUNT(*) AS nb_entrees FROM menu_options WHERE menu_id = @menu_entreprise AND type = 'entree';
