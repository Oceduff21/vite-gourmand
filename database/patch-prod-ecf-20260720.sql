-- Pack SQL prod InfinityFree — executer dans l'ordre dans phpMyAdmin
-- (ignorer "Duplicate column" si deja applique)

-- 1) Colonnes boissons si absentes
ALTER TABLE boissons ADD COLUMN categorie VARCHAR(50) DEFAULT 'soft' AFTER nom;
ALTER TABLE boissons ADD COLUMN description VARCHAR(255) DEFAULT NULL AFTER prix;

-- 2) Prix Menu Entreprise
UPDATE menus
SET prix = 34.00, min_personnes = 10, stock = 40, delai_jours = 5, theme = 'Business', titre = 'Menu Entreprise'
WHERE id = 14 OR LOWER(titre) LIKE '%entreprise%';

-- 3) Boissons categories (noms catalogue)
UPDATE boissons SET nom = 'Coca-Cola 33cl', prix = 2.50, categorie = 'soft', description = 'Boisson gazeuse' WHERE id = 1;
UPDATE boissons SET nom = 'Orangina 33cl', prix = 2.50, categorie = 'soft', description = 'Boisson gazeuse' WHERE id = 2;
UPDATE boissons SET nom = 'Eau minerale 50cl', prix = 1.80, categorie = 'eau', description = 'Bouteille individuelle' WHERE id = 3;
UPDATE boissons SET nom = 'Eau petillante 75cl', prix = 3.00, categorie = 'eau', description = 'Bouteille individuelle' WHERE id = 4;
UPDATE boissons SET nom = 'Jus d orange frais', prix = 3.50, categorie = 'jus', description = 'Presse ou artisanal' WHERE id = 5;
UPDATE boissons SET nom = 'Jus de pomme', prix = 3.00, categorie = 'jus', description = 'Presse ou artisanal' WHERE id = 6;
UPDATE boissons SET nom = 'Virgin Mojito', prix = 4.50, categorie = 'cocktail_sans_alcool' WHERE id = 7;
UPDATE boissons SET nom = 'Limonade artisanale', prix = 3.20, categorie = 'soft' WHERE id = 8;
UPDATE boissons SET nom = 'Biere blonde 33cl', prix = 4.00, categorie = 'biere' WHERE id = 9;
UPDATE boissons SET nom = 'Biere brune 33cl', prix = 4.20, categorie = 'biere' WHERE id = 10;
UPDATE boissons SET nom = 'Vin rouge Bordeaux AOC', prix = 18.00, categorie = 'vin_rouge' WHERE id = 11;
UPDATE boissons SET nom = 'Vin rouge Medoc', prix = 22.00, categorie = 'vin_rouge' WHERE id = 12;
UPDATE boissons SET nom = 'Vin blanc Entre-deux-Mers', prix = 16.00, categorie = 'vin_blanc' WHERE id = 13;
UPDATE boissons SET nom = 'Vin blanc Sancerre', prix = 24.00, categorie = 'vin_blanc' WHERE id = 14;
UPDATE boissons SET nom = 'Vin rose de Provence', prix = 17.00, categorie = 'vin_rose' WHERE id = 15;
UPDATE boissons SET nom = 'Champagne Brut', prix = 45.00, categorie = 'champagne' WHERE id = 16;
UPDATE boissons SET nom = 'Champagne Rose', prix = 52.00, categorie = 'champagne' WHERE id = 17;
UPDATE boissons SET nom = 'Whisky 4cl', prix = 6.00, categorie = 'spiritueux' WHERE id = 18;
UPDATE boissons SET nom = 'Rhum 4cl', prix = 5.50, categorie = 'spiritueux' WHERE id = 19;
UPDATE boissons SET nom = 'Cafe expresso', prix = 2.00, categorie = 'autre', description = 'Pause cafe' WHERE id = 20;

SELECT id, titre, prix FROM menus WHERE id = 14;
SELECT id, nom, categorie, prix FROM boissons ORDER BY id LIMIT 5;
