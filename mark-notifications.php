<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/user-helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        markNotificationRead($pdo, $userId, $id);
    } else {
        markAllNotificationsRead($pdo, $userId);
    }
}

header('Location: espace-utilisateur.php?tab=notifications');
exit();
