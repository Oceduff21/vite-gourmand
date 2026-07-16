<?php

$config = [];
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    $config = require $localConfig;
}

$host = $config['host'] ?? (getenv('DB_HOST') ?: 'localhost');
$dbname = $config['dbname'] ?? (getenv('DB_NAME') ?: 'vite_gourmand');
$user = $config['user'] ?? (getenv('DB_USER') ?: 'root');
$password = array_key_exists('password', $config)
    ? $config['password']
    : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch(PDOException $e) {

    $msg = file_exists(__DIR__ . '/config.local.php')
        ? 'Erreur connexion base de donnees. Verifiez includes/config.local.php'
        : 'Erreur connexion base de donnees.';
    die($msg);

}