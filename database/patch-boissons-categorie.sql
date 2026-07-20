-- Patch boissons : colonnes categorie + description (local / post-catalogue)
-- Executer apres catalogue-complet.sql

ALTER TABLE boissons ADD COLUMN categorie VARCHAR(50) DEFAULT 'soft' AFTER nom;
ALTER TABLE boissons ADD COLUMN description VARCHAR(255) DEFAULT NULL AFTER prix;

-- Catalogue complet (ids 1-20)
UPDATE boissons SET nom = 'Coca-Cola 33cl', prix = 2.50, categorie = 'soft', description = 'Boisson gazeuse' WHERE id = 1;
UPDATE boissons SET nom = 'Orangina 33cl', prix = 2.50, categorie = 'soft', description = 'Boisson gazeuse' WHERE id = 2;
UPDATE boissons SET nom = 'Eau minerale 50cl', prix = 1.80, categorie = 'eau', description = 'Bouteille individuelle' WHERE id = 3;
UPDATE boissons SET nom = 'Eau petillante 75cl', prix = 3.00, categorie = 'eau', description = 'Bouteille individuelle' WHERE id = 4;
UPDATE boissons SET nom = 'Jus d orange frais', prix = 3.50, categorie = 'jus', description = 'Presse ou artisanal' WHERE id = 5;
UPDATE boissons SET nom = 'Jus de pomme', prix = 3.00, categorie = 'jus', description = 'Presse ou artisanal' WHERE id = 6;
UPDATE boissons SET nom = 'Virgin Mojito', prix = 4.50, categorie = 'cocktail_sans_alcool', description = NULL WHERE id = 7;
UPDATE boissons SET nom = 'Limonade artisanale', prix = 3.20, categorie = 'soft', description = 'Boisson gazeuse' WHERE id = 8;
UPDATE boissons SET nom = 'Biere blonde 33cl', prix = 4.00, categorie = 'biere', description = NULL WHERE id = 9;
UPDATE boissons SET nom = 'Biere brune 33cl', prix = 4.20, categorie = 'biere', description = NULL WHERE id = 10;
UPDATE boissons SET nom = 'Vin rouge Bordeaux AOC', prix = 18.00, categorie = 'vin_rouge', description = NULL WHERE id = 11;
UPDATE boissons SET nom = 'Vin rouge Medoc', prix = 22.00, categorie = 'vin_rouge', description = NULL WHERE id = 12;
UPDATE boissons SET nom = 'Vin blanc Entre-deux-Mers', prix = 16.00, categorie = 'vin_blanc', description = NULL WHERE id = 13;
UPDATE boissons SET nom = 'Vin blanc Sancerre', prix = 24.00, categorie = 'vin_blanc', description = NULL WHERE id = 14;
UPDATE boissons SET nom = 'Vin rose de Provence', prix = 17.00, categorie = 'vin_rose', description = NULL WHERE id = 15;
UPDATE boissons SET nom = 'Champagne Brut', prix = 45.00, categorie = 'champagne', description = NULL WHERE id = 16;
UPDATE boissons SET nom = 'Champagne Rose', prix = 52.00, categorie = 'champagne', description = NULL WHERE id = 17;
UPDATE boissons SET nom = 'Whisky 4cl', prix = 6.00, categorie = 'spiritueux', description = NULL WHERE id = 18;
UPDATE boissons SET nom = 'Rhum 4cl', prix = 5.50, categorie = 'spiritueux', description = NULL WHERE id = 19;
UPDATE boissons SET nom = 'Cafe expresso', prix = 2.00, categorie = 'autre', description = 'Pause cafe' WHERE id = 20;

-- Liens menu_boissons (tous menus sauf enfant 11 = sans alcool)
DELETE FROM menu_boissons WHERE menu_id BETWEEN 1 AND 14;

INSERT INTO menu_boissons (menu_id, boisson_id)
SELECT m.id, b.id FROM menus m CROSS JOIN boissons b
WHERE m.id BETWEEN 1 AND 14 AND m.id <> 11;

INSERT INTO menu_boissons (menu_id, boisson_id)
SELECT 11, id FROM boissons WHERE id IN (1,2,3,4,5,6,7,8,20);
