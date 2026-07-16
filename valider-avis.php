<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: login.php');
    exit();
}

$id = (int)($_POST['avis_id'] ?? $_GET['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare('UPDATE avis SET is_validated = 1 WHERE id = ?')->execute([$id]);
}

header('Location: admin/admin-avis.php');
exit();
