-- Reinitialise le mot de passe admin Jose (Admin123!)
-- Preferer patch-cleanup-users.sql pour un tri complet des comptes.

UPDATE users
SET password = '$2y$10$5ug3pkSriD0X503uEwIVmuo480xxHKzX0wzLf2R/v/Z2QC2bvZlM.',
    role = 'admin',
    is_active = 1
WHERE email = 'jose@vite-gourmand.fr';

INSERT INTO users (nom, prenom, email, gsm, telephone, rue, numero, code_postal, ville, password, role, is_active)
SELECT 'Gourmand', 'Jose', 'jose@vite-gourmand.fr', '0622334455', '0622334455', 'Rue Verteuil', '11', '33000', 'Bordeaux',
       '$2y$10$5ug3pkSriD0X503uEwIVmuo480xxHKzX0wzLf2R/v/Z2QC2bvZlM.', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'jose@vite-gourmand.fr');
