-- Migration v2 compatible InfinityFree / MySQL 5.7+
-- Executer UNE FOIS dans phpMyAdmin (ignorer erreurs "Duplicate column")

ALTER TABLE menus ADD COLUMN slogan VARCHAR(255) DEFAULT NULL AFTER titre;
ALTER TABLE menus ADD COLUMN image_principale VARCHAR(255) DEFAULT NULL AFTER description;
ALTER TABLE menus ADD COLUMN type_evenement VARCHAR(100) DEFAULT NULL AFTER theme;
ALTER TABLE menus ADD COLUMN recommandations TEXT DEFAULT NULL AFTER conditions;
ALTER TABLE menus ADD COLUMN informations_pratiques TEXT DEFAULT NULL AFTER recommandations;

ALTER TABLE boissons ADD COLUMN categorie VARCHAR(50) DEFAULT 'soft' AFTER nom;
ALTER TABLE boissons ADD COLUMN description VARCHAR(255) DEFAULT NULL AFTER prix;

CREATE TABLE IF NOT EXISTS commande_boissons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    boisson_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (boisson_id) REFERENCES boissons(id)
);
