CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    gsm VARCHAR(20),
    adresse TEXT,
    password VARCHAR(255),
    role ENUM('utilisateur','employe','admin') DEFAULT 'utilisateur',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255),
    description TEXT,
    theme VARCHAR(100),
    regime VARCHAR(100),
    prix DECIMAL(10,2),
    min_personnes INT,
    stock INT
);

CREATE TABLE plats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255),
    type ENUM('entree','plat','dessert')
);

CREATE TABLE menu_plats (
    menu_id INT,
    plat_id INT,
    PRIMARY KEY(menu_id, plat_id)
);

CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    menu_id INT,
    nb_personnes INT,
    date_livraison DATE,
    heure_livraison TIME,
    adresse TEXT,
    statut VARCHAR(50),
    prix_total DECIMAL(10,2)
);

CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    note INT,
    commentaire TEXT,
    valide BOOLEAN DEFAULT FALSE
);
