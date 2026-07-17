-- Corriger prix Menu Entreprise (id 14) si affiche 0.00 EUR sur menu.php
-- Executer dans phpMyAdmin InfinityFree

UPDATE menus
SET
  prix = 34.00,
  min_personnes = 10,
  stock = 40,
  delai_jours = 5,
  theme = 'Business',
  titre = 'Menu Entreprise'
WHERE id = 14 OR LOWER(titre) LIKE '%entreprise%';

SELECT id, titre, prix, min_personnes, stock, delai_jours
FROM menus
WHERE id = 14 OR LOWER(titre) LIKE '%entreprise%';
