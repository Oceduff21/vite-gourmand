<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== Diagnostic Vite & Gourmand ===\n\n";

echo 'PHP: ' . PHP_VERSION . "\n";
echo 'MongoDB ext: ' . (class_exists('MongoDB\Driver\Manager') ? 'oui' : 'non') . "\n\n";

$configFile = __DIR__ . '/includes/config.local.php';
echo 'config.local.php: ' . (is_file($configFile) ? 'OK' : 'MANQUANT') . "\n";

$required = [
    'index.php',
    'menus.php',
    'login.php',
    'admin/login.php',
    'includes/db.php',
    'assets/css/style.css',
];

foreach ($required as $file) {
    $path = __DIR__ . '/' . $file;
    if (!file_exists($path)) {
        echo "$file: MANQUANT\n";
    } elseif (is_dir($path)) {
        echo "$file: ERREUR (c'est un dossier, pas un fichier)\n";
    } else {
        echo "$file: OK\n";
    }
}

echo "\n--- BDD ---\n";
try {
    require __DIR__ . '/includes/db.php';
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Connexion BDD: OK\n";
    echo 'Tables: ' . count($tables) . "\n";
    echo 'Menus: ' . (int)$pdo->query('SELECT COUNT(*) FROM menus')->fetchColumn() . "\n";
    echo 'Users: ' . (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() . "\n";
} catch (Throwable $e) {
    echo 'Connexion BDD: ERREUR - ' . $e->getMessage() . "\n";
}

echo "\n--- Admin login test ---\n";
$adminLogin = __DIR__ . '/admin/login.php';
if (is_file($adminLogin)) {
    echo 'admin/login.php lisible: OK (' . filesize($adminLogin) . " octets)\n";
} else {
    echo "admin/login.php: probleme (fichier absent ou dossier)\n";
}

echo "\nSupprimez ce fichier apres le test.\n";
