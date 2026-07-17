-- Photos de couverture par menu (galerie + cartes)
-- Executer apres migration-v2-infinityfree.sql

-- Supprimer les images generiques en BDD qui bloquaient les presets theme
-- (InfinityFree : colonne image_principale uniquement, pas de colonne image)
UPDATE menus SET image_principale = NULL
WHERE image_principale IN ('presentation.jpg', 'default.jpg', 'image.jpg', '');

-- Fichiers reels dans assets/images/ (pas menu-noel.jpg ni event.jpg)
UPDATE menus SET image_principale = 'menu-noel-entree.jpg' WHERE LOWER(titre) LIKE '%noel%' OR LOWER(titre) LIKE '%noël%';
UPDATE menus SET image_principale = 'menu-prestige.jpg' WHERE LOWER(titre) LIKE '%mariage%';
UPDATE menus SET image_principale = 'menu-classique.jpg' WHERE LOWER(titre) LIKE '%paques%' OR LOWER(titre) LIKE '%pâques%';
UPDATE menus SET image_principale = 'menu-vegan.jpg' WHERE LOWER(titre) LIKE '%vegan%';
UPDATE menus SET image_principale = 'menu-business.jpg' WHERE LOWER(titre) LIKE '%business%' OR LOWER(titre) LIKE '%entreprise%';
UPDATE menus SET image_principale = 'menu-bapteme.jpg' WHERE LOWER(titre) LIKE '%anniversaire%' OR LOWER(titre) LIKE '%bapteme%' OR LOWER(titre) LIKE '%baptême%';
UPDATE menus SET image_principale = 'menu-gastronomique.jpg' WHERE LOWER(titre) LIKE '%gastro%';
UPDATE menus SET image_principale = 'menu-prestige.jpg' WHERE LOWER(titre) LIKE '%prestige%';
UPDATE menus SET image_principale = 'menu-cocktail.jpg' WHERE LOWER(titre) LIKE '%cocktail%';
UPDATE menus SET image_principale = 'menu-oriental.jpg' WHERE LOWER(titre) LIKE '%oriental%' OR LOWER(titre) LIKE '%mediterr%' OR LOWER(titre) LIKE '%portugais%';
UPDATE menus SET image_principale = 'menu-brunch.jpg' WHERE LOWER(titre) LIKE '%brunch%';
UPDATE menus SET image_principale = 'menu-enfant.jpg' WHERE LOWER(titre) LIKE '%enfant%';
UPDATE menus SET image_principale = 'menu-entreprise.jpg' WHERE id = 14 OR LOWER(titre) LIKE '%entreprise%';

-- Images plats : eviter presentation.jpg generique sur entrees
UPDATE plats SET image = 'veloute.jpg' WHERE id IN (1, 17) AND image IN ('presentation.jpg', 'default.jpg', 'image.jpg');
UPDATE plats SET image = 'salade.jpg' WHERE id IN (16, 41) AND image IN ('presentation.jpg', 'default.jpg');
UPDATE plats SET image = 'fruits.jpg' WHERE id IN (42, 43, 44) AND image = 'presentation.jpg';
