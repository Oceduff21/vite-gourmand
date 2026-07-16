<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare('DELETE FROM plats WHERE id = ?')->execute([$id]);
}

header('Location: admin-plats.php');
exit();
