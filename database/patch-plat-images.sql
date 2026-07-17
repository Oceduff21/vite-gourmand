-- Ancien patch partiel — preferer : patch-plat-images-complet.sql
-- (Ce fichier applique les corrections de base si vous ne lancez pas le complet)

UPDATE plats SET image = 'gazpacho.jpg' WHERE id = 16;
UPDATE plats SET image = 'bar.jpg' WHERE id = 24;
UPDATE plats SET image = 'lasagne.jpg' WHERE id = 26;
UPDATE plats SET image = 'tarte-citron.jpg' WHERE id = 32;
UPDATE plats SET image = 'financier.jpg' WHERE id = 33;
UPDATE plats SET image = 'burger-enfant.jpg' WHERE id = 34;
UPDATE plats SET image = 'nuggets.jpg' WHERE id = 35;
UPDATE plats SET image = 'pates.jpg' WHERE id = 36;
UPDATE plats SET image = 'merguez.jpg' WHERE id = 38;
UPDATE plats SET image = 'piri-piri.jpg' WHERE id = 39;
UPDATE plats SET image = 'bacalhau.jpg' WHERE id = 40;
UPDATE plats SET image = 'mezze.jpg' WHERE id = 41;
UPDATE plats SET image = 'brunch-plateau.jpg' WHERE id = 42;
UPDATE plats SET image = 'pancakes.jpg' WHERE id = 43;
UPDATE plats SET image = 'clafoutis.jpg' WHERE id = 44;
UPDATE plats SET image = 'curry.jpg' WHERE id = 22;

UPDATE plats SET image = 'veloute.jpg' WHERE image IN ('presentation.jpg', 'default.jpg', 'image.jpg');
