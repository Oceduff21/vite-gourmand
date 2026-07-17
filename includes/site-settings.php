<?php

function ensureSiteSettingsTable(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function getSiteSetting(PDO $pdo, string $key, string $default = ''): string
{
    ensureSiteSettingsTable($pdo);
    $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? (string)$row['setting_value'] : $default;
}

function setSiteSetting(PDO $pdo, string $key, string $value): void
{
    ensureSiteSettingsTable($pdo);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$key, $value]);
}

function getDefaultHoraires(): array
{
    return [
        'horaire_lv' => 'Lundi - Vendredi : 9h - 19h',
        'horaire_samedi' => 'Samedi : 10h - 18h',
        'horaire_dimanche' => 'Dimanche : 10h - 14h',
    ];
}

function getHoraires(PDO $pdo): array
{
    $defaults = getDefaultHoraires();
    return [
        'horaire_lv' => getSiteSetting($pdo, 'horaire_lv', $defaults['horaire_lv']),
        'horaire_samedi' => getSiteSetting($pdo, 'horaire_samedi', $defaults['horaire_samedi']),
        'horaire_dimanche' => getSiteSetting($pdo, 'horaire_dimanche', $defaults['horaire_dimanche']),
    ];
}

function getFooterHoraires(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $defaults = array_values(getDefaultHoraires());
    try {
        if (!isset($GLOBALS['pdo'])) {
            require_once __DIR__ . '/db.php';
        }
        $cached = array_values(getHoraires($GLOBALS['pdo']));
    } catch (Throwable $e) {
        $cached = $defaults;
    }

    return $cached;
}
