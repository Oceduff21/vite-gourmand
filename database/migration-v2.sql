-- Migration v2 : enrichissement catalogue (executer sur BDD existante)

ALTER TABLE menus
    ADD COLUMN IF NOT EXISTS slogan VARCHAR(255) DEFAULT NULL AFTER titre,
    ADD COLUMN IF NOT EXISTS image_principale VARCHAR(255) DEFAULT NULL AFTER description,
    ADD COLUMN IF NOT EXISTS type_evenement VARCHAR(100) DEFAULT NULL AFTER theme,
    ADD COLUMN IF NOT EXISTS recommandations TEXT DEFAULT NULL AFTER conditions,
    ADD COLUMN IF NOT EXISTS informations_pratiques TEXT DEFAULT NULL AFTER recommandations;

ALTER TABLE boissons
    ADD COLUMN IF NOT EXISTS categorie VARCHAR(50) DEFAULT 'soft' AFTER nom,
    ADD COLUMN IF NOT EXISTS description VARCHAR(255) DEFAULT NULL AFTER prix;

CREATE TABLE IF NOT EXISTS commande_boissons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    boisson_id INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (boisson_id) REFERENCES boissons(id)
);
