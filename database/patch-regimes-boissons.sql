-- Regimes equilibres (veg/vegan + sans gluten/lactose + viande/poisson) + boissons completes
-- Importer dans phpMyAdmin apres catalogue-complet.sql

DELETE FROM menu_options WHERE menu_id BETWEEN 1 AND 14;

INSERT INTO menu_options (menu_id, plat_id, type) VALUES
-- entree: veg | sans gluten/lactose | viande/poisson
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

-- Toutes les boissons pour tous les menus sauf Menu Enfant (11) = sans alcool uniquement
DELETE FROM menu_boissons WHERE menu_id BETWEEN 1 AND 14;

INSERT INTO menu_boissons (menu_id, boisson_id)
SELECT m.id, b.id FROM menus m CROSS JOIN boissons b
WHERE m.id BETWEEN 1 AND 14 AND m.id <> 11;

INSERT INTO menu_boissons (menu_id, boisson_id)
SELECT 11, id FROM boissons WHERE id IN (1,2,3,4,5,6,7,8,20);
