-- Nettoyage des comptes : 3 comptes de demo uniquement
-- A executer dans phpMyAdmin (local ou InfinityFree).
--
-- Comptes conserves :
--   jose@vite-gourmand.fr      admin       Admin123!
--   julie@vite-gourmand.fr     employe     Employe123!
--   client@vite-gourmand.fr    utilisateur Client123!

-- 1. Creer / mettre a jour les 3 comptes canoniques
INSERT INTO users (nom, prenom, email, gsm, telephone, rue, numero, code_postal, ville, password, role, is_active)
VALUES
    ('Gourmand', 'Jose', 'jose@vite-gourmand.fr', '0622334455', '0622334455', 'Rue Verteuil', '11', '33000', 'Bordeaux',
     '$2y$10$5ug3pkSriD0X503uEwIVmuo480xxHKzX0wzLf2R/v/Z2QC2bvZlM.', 'admin', 1),
    ('Gourmand', 'Julie', 'julie@vite-gourmand.fr', '0611223344', '0611223344', 'Rue Verteuil', '11', '33000', 'Bordeaux',
     '$2y$10$DpEVJNnWM82SRquJ1PLrbOjR2YOxYtYqgC.7mkDQX1voTchdh7odG', 'employe', 1),
    ('Dupont', 'Marie', 'client@vite-gourmand.fr', '0633445566', '0633445566', 'Cours Victor Hugo', '25', '33000', 'Bordeaux',
     '$2y$10$Xwsh9yOlEtPKKnvaWLvrSOeZQ2KflrwJbgRhZU5xQsNCqPr26nQLS', 'utilisateur', 1)
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    prenom = VALUES(prenom),
    gsm = VALUES(gsm),
    telephone = VALUES(telephone),
    rue = VALUES(rue),
    numero = VALUES(numero),
    code_postal = VALUES(code_postal),
    ville = VALUES(ville),
    password = VALUES(password),
    role = VALUES(role),
    is_active = 1;

-- 2. Reassigner commandes / avis des comptes supprimes vers le client demo
UPDATE commandes c
JOIN users u ON c.user_id = u.id
SET c.user_id = (SELECT id FROM (SELECT id FROM users WHERE email = 'client@vite-gourmand.fr' LIMIT 1) AS t)
WHERE u.email NOT IN ('jose@vite-gourmand.fr', 'julie@vite-gourmand.fr', 'client@vite-gourmand.fr');

UPDATE avis a
JOIN users u ON a.user_id = u.id
SET a.user_id = (SELECT id FROM (SELECT id FROM users WHERE email = 'client@vite-gourmand.fr' LIMIT 1) AS t)
WHERE u.email NOT IN ('jose@vite-gourmand.fr', 'julie@vite-gourmand.fr', 'client@vite-gourmand.fr');

-- 3. Supprimer donnees liees aux autres comptes
DELETE f FROM user_favoris f
JOIN users u ON f.user_id = u.id
WHERE u.email NOT IN ('jose@vite-gourmand.fr', 'julie@vite-gourmand.fr', 'client@vite-gourmand.fr');

DELETE n FROM notifications n
JOIN users u ON n.user_id = u.id
WHERE u.email NOT IN ('jose@vite-gourmand.fr', 'julie@vite-gourmand.fr', 'client@vite-gourmand.fr');

DELETE pr FROM password_resets pr
WHERE pr.email NOT IN ('jose@vite-gourmand.fr', 'julie@vite-gourmand.fr', 'client@vite-gourmand.fr');

-- 4. Supprimer tous les autres utilisateurs
DELETE FROM users
WHERE email NOT IN ('jose@vite-gourmand.fr', 'julie@vite-gourmand.fr', 'client@vite-gourmand.fr');
