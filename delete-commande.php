<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: login.php');
    exit();
}
header('Location: admin/delete-commande.php?' . http_build_query(['id' => $_GET['id'] ?? '']));
exit();
