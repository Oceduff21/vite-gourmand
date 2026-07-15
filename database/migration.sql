-- Migration Vite & Gourmand (base existante)
USE vite_gourmand;

UPDATE commandes SET statut = 'en_attente' WHERE statut = 'en attente';

ALTER TABLE avis ADD COLUMN IF NOT EXISTS commande_id INT NULL;
ALTER TABLE avis ADD COLUMN IF NOT EXISTS is_validated TINYINT(1) DEFAULT 0;
ALTER TABLE avis ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
UPDATE avis SET is_validated = valide WHERE is_validated = 0 AND valide = 1;

ALTER TABLE users ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS date_naissance DATE NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS rue VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS numero VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS complement VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS code_postal VARCHAR(10) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS ville VARCHAR(100) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

ALTER TABLE commandes ADD COLUMN IF NOT EXISTS rue VARCHAR(255) NULL;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS numero VARCHAR(20) NULL;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS complement VARCHAR(255) NULL;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS code_postal VARCHAR(10) NULL;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS ville VARCHAR(100) NULL;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS prix_menu DECIMAL(10,2) DEFAULT 0;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS prix_livraison DECIMAL(10,2) DEFAULT 0;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS reduction DECIMAL(10,2) DEFAULT 0;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE menus ADD COLUMN IF NOT EXISTS conditions TEXT NULL;
ALTER TABLE menus ADD COLUMN IF NOT EXISTS delai_jours INT DEFAULT 7;

ALTER TABLE plats ADD COLUMN IF NOT EXISTS description TEXT NULL;
ALTER TABLE plats ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT 'default.jpg';
ALTER TABLE plats ADD COLUMN IF NOT EXISTS regime VARCHAR(50) DEFAULT 'classique';
ALTER TABLE plats ADD COLUMN IF NOT EXISTS allergenes TEXT NULL;

CREATE TABLE IF NOT EXISTS boissons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prix DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS menu_options (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    type ENUM('entree','plat','dessert') NOT NULL,
    PRIMARY KEY (menu_id, plat_id)
);

CREATE TABLE IF NOT EXISTS menu_boissons (
    menu_id INT NOT NULL,
    boisson_id INT NOT NULL,
    PRIMARY KEY (menu_id, boisson_id)
);

CREATE TABLE IF NOT EXISTS commande_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    plat_id INT NOT NULL,
    quantite INT NOT NULL,
    type ENUM('entree','plat','dessert') NOT NULL
);

CREATE TABLE IF NOT EXISTS commande_historique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    statut VARCHAR(50) NOT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE commande_historique ADD COLUMN IF NOT EXISTS note TEXT NULL;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO users (nom, prenom, email, gsm, telephone, rue, numero, code_postal, ville, password, role, is_active)
VALUES ('Admin', 'Vite', 'admin@vite-gourmand.fr', '0611223344', '0611223344', 'Rue Verteuil', '11', '33000', 'Bordeaux',
'$2y$10$JfmsCrP1S2liy3hWG8Nwa.q1jmejbv3AP5cr4hrlyS3UmxHaoTUau', 'admin', 1);
