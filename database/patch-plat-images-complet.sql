-- Mise a jour complete des images plats (photos uploadees dans assets/images/)
-- Executer dans phpMyAdmin apres avoir uploade le dossier assets/images/

-- ========== PLATS DE BASE (seed id 1-9) ==========
UPDATE plats SET image = 'veloute.jpg' WHERE id = 1;
UPDATE plats SET image = 'salade.jpg' WHERE id = 2;
UPDATE plats SET image = 'poulet.jpg' WHERE id = 3;
UPDATE plats SET image = 'risotto-champignons.jpg' WHERE id = 4;
UPDATE plats SET image = 'tiramisu.jpg' WHERE id = 5;
UPDATE plats SET image = 'fruits.jpg' WHERE id = 6;
UPDATE plats SET image = 'foie-gras.jpg' WHERE id = 7;
UPDATE plats SET image = 'saumon.jpg' WHERE id = 8;
UPDATE plats SET image = 'fondant.jpg' WHERE id = 9;

-- ========== PLATS CATALOGUE (id 10-44) ==========
UPDATE plats SET image = 'carpaccio-boeuf.jpg' WHERE id = 10;
UPDATE plats SET image = 'veloute-potimarron.jpg' WHERE id = 11;
UPDATE plats SET image = 'salade-quinoa.jpg' WHERE id = 12;
UPDATE plats SET image = 'brochettes-poulet.jpg' WHERE id = 13;
UPDATE plats SET image = 'tartare-saumon.jpg' WHERE id = 14;
UPDATE plats SET image = 'terrine-canard.jpg' WHERE id = 15;
UPDATE plats SET image = 'gazpacho.jpg' WHERE id = 16;
UPDATE plats SET image = 'veloute-champignons.jpg' WHERE id = 17;
UPDATE plats SET image = 'filet-boeuf.jpg' WHERE id = 18;
UPDATE plats SET image = 'saumon.jpg' WHERE id = 19;
UPDATE plats SET image = 'poulet-halal.jpg' WHERE id = 20;
UPDATE plats SET image = 'risotto-truffes.jpg' WHERE id = 21;
UPDATE plats SET image = 'curry.jpg' WHERE id = 22;
UPDATE plats SET image = 'canard.jpg' WHERE id = 23;
UPDATE plats SET image = 'bar.jpg' WHERE id = 24;
UPDATE plats SET image = 'poulet-basquaise.jpg' WHERE id = 25;
UPDATE plats SET image = 'lasagne.jpg' WHERE id = 26;
UPDATE plats SET image = 'buche.jpg' WHERE id = 27;
UPDATE plats SET image = 'tiramisu.jpg' WHERE id = 28;
UPDATE plats SET image = 'mousse-chocolat.jpg' WHERE id = 29;
UPDATE plats SET image = 'fruits.jpg' WHERE id = 30;
UPDATE plats SET image = 'fondant.jpg' WHERE id = 31;
UPDATE plats SET image = 'tarte-citron.jpg' WHERE id = 32;
UPDATE plats SET image = 'financier.jpg' WHERE id = 33;
UPDATE plats SET image = 'burger-enfant.jpg' WHERE id = 34;
UPDATE plats SET image = 'nuggets.jpg' WHERE id = 35;
UPDATE plats SET image = 'pates.jpg' WHERE id = 36;
UPDATE plats SET image = 'boeuf.jpg' WHERE id = 37;
UPDATE plats SET image = 'merguez.jpg' WHERE id = 38;
UPDATE plats SET image = 'piri-piri.jpg' WHERE id = 39;
UPDATE plats SET image = 'bacalhau.jpg' WHERE id = 40;
UPDATE plats SET image = 'mezze.jpg' WHERE id = 41;
UPDATE plats SET image = 'brunch-plateau.jpg' WHERE id = 42;
UPDATE plats SET image = 'pancakes.jpg' WHERE id = 43;
UPDATE plats SET image = 'clafoutis.jpg' WHERE id = 44;

-- Nettoyer les images generiques oubliees
UPDATE plats SET image = 'veloute.jpg' WHERE image IN ('presentation.jpg', 'default.jpg', 'image.jpg');

-- Verification :
-- SELECT id, nom, type, image FROM plats ORDER BY type, id;
