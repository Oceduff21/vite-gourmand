-- Horaires du site (pied de page) — table cles/valeurs
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
('horaire_lv', 'Lundi - Vendredi : 9h - 19h'),
('horaire_samedi', 'Samedi : 10h - 18h'),
('horaire_dimanche', 'Dimanche : 10h - 14h');
