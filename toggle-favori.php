<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/user-helpers.php';
require 'includes/flash.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$menuId = (int)($_POST['menu_id'] ?? 0);
$redirect = $_POST['redirect'] ?? 'espace-utilisateur.php?tab=favoris';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('Session expiree. Rechargez la page et reessayez.');
    header('Location: ' . $redirect);
    exit();
}

if ($menuId > 0) {
    $result = toggleUserFavori($pdo, (int)$_SESSION['user_id'], $menuId);
    if ($result === null) {
        setFlash('Favoris indisponibles : importez migration-user-space.sql dans phpMyAdmin.');
    } elseif ($result) {
        setFlash('Menu ajoute a vos favoris.');
    } else {
        setFlash('Menu retire de vos favoris.');
    }
}

header('Location: ' . $redirect);
exit();
