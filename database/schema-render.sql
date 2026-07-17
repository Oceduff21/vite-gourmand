-- Import sur hebergeur MySQL distant (db4free, Railway, etc.)
-- NE PAS utiliser CREATE DATABASE / USE : la BDD existe deja

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    gsm VARCHAR(20) DEFAULT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    date_naissance DATE DEFAULT NULL,
    rue VARCHAR(255) DEFAULT NULL,
    numero VARCHAR(20) DEFAULT NULL,
    complement VARCHAR(255) DEFAULT NULL,
    code_postal VARCHAR(10) DEFAULT NULL,
    ville VARCHAR(100) DEFAULT NULL,
    adresse TEXT DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('utilisateur','employe','admin') DEFAULT 'utilisateur',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    theme VARCHAR(100),
    regime VARCHAR(100),
    prix DECIMAL(10,2) NOT NULL,
    min_personnes INT NOT NULL DEFAULT 1,
    stock INT DEFAULT 0,
    conditions TEXT,
    delai_jours INT DEFAULT 7
);

CREATE TABLE IF NOT EXISTS plats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    type ENUM('entree','plat','dessert') NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT 'default.jpg',
    regime VARCHAR(50) DEFAULT 'classique',
    allergenes TEXT
);

CREATE TABLE IF NOT EXISTS boissons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prix DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS menu_options (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    type ENUM('entree','plat','dessert') NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS menu_boissons (
    menu_id INT NOT NULL,
    boisson_id INT NOT NULL,
    PRIMARY KEY (menu_id, boisson_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (boisson_id) REFERENCES boissons(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_id INT NOT NULL,
    nb_personnes INT NOT NULL,
    date_livraison DATE NOT NULL,
    heure_livraison TIME NOT NULL,
    rue VARCHAR(255) NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complement VARCHAR(255) DEFAULT NULL,
    code_postal VARCHAR(10) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    statut VARCHAR(50) DEFAULT 'en_attente',
    prix_menu DECIMAL(10,2) DEFAULT 0,
    prix_livraison DECIMAL(10,2) DEFAULT 0,
    reduction DECIMAL(10,2) DEFAULT 0,
    prix_total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

CREATE TABLE IF NOT EXISTS commande_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    plat_id INT NOT NULL,
    quantite INT NOT NULL,
    type ENUM('entree','plat','dessert') NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plats(id)
);

CREATE TABLE IF NOT EXISTS commande_historique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    statut VARCHAR(50) NOT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    commande_id INT DEFAULT NULL,
    note INT NOT NULL,
    commentaire TEXT,
    is_validated TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (commande_id) REFERENCES commandes(id)
);

INSERT IGNORE INTO users (nom, prenom, email, gsm, telephone, rue, numero, code_postal, ville, password, role, is_active)
VALUES
('Gourmand', 'Jose', 'jose@vite-gourmand.fr', '0622334455', '0622334455', 'Rue Verteuil', '11', '33000', 'Bordeaux',
 '$2y$10$5ug3pkSriD0X503uEwIVmuo480xxHKzX0wzLf2R/v/Z2QC2bvZlM.', 'admin', 1),
('Gourmand', 'Julie', 'julie@vite-gourmand.fr', '0611223344', '0611223344', 'Rue Verteuil', '11', '33000', 'Bordeaux',
 '$2y$10$DpEVJNnWM82SRquJ1PLrbOjR2YOxYtYqgC.7mkDQX1voTchdh7odG', 'employe', 1),
('Dupont', 'Marie', 'client@vite-gourmand.fr', '0633445566', '0633445566', 'Cours Victor Hugo', '25', '33000', 'Bordeaux',
 '$2y$10$Xwsh9yOlEtPKKnvaWLvrSOeZQ2KflrwJbgRhZU5xQsNCqPr26nQLS', 'utilisateur', 1);
