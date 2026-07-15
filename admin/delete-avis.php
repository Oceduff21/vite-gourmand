<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: admin-avis.php');
    exit();
}

$id = (int)$_GET['id'];

$pdo->prepare('DELETE FROM avis WHERE id = ?')->execute([$id]);

header('Location: admin-avis.php');
exit();
