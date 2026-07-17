-- Photo manquante : Curry legumes coco (plat id 22)
--
-- 1. Uploadez la photo dans : htdocs/assets/images/curry.jpg
--    (recommande : 800x600 px, plat vegan coco / legumes)
-- 2. Executez ce script dans phpMyAdmin

UPDATE plats
SET image = 'curry.jpg'
WHERE id = 22;

-- Verification :
-- SELECT id, nom, image FROM plats WHERE id = 22;
