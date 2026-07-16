-- Nombre d'enfants dans une commande (menus adultes avec portion enfant)
ALTER TABLE commandes ADD COLUMN nb_enfants INT NOT NULL DEFAULT 0;
