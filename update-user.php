<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/user-helpers.php';

if (is_file(__DIR__ . '/includes/flash.php')) {
    require 'includes/flash.php';
} else {
    function setFlash(string $message, string $type = 'success'): void {
        $_SESSION['flash'] = $message;
        $_SESSION['flash_type'] = $type;
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$redirect = 'espace-utilisateur.php?tab=profil';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit();
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    setFlash('Session expiree. Rechargez la page et reessayez.');
    header('Location: ' . $redirect);
    exit();
}

$result = saveUserProfile($pdo, (int)$_SESSION['user_id'], $_POST);

if ($result['ok']) {
    setFlash('Profil mis a jour avec succes.');
} else {
    setFlash($result['error'] ?? 'Erreur lors de la mise a jour du profil.', 'danger');
}

header('Location: ' . $redirect);
exit();
