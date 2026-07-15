<?php
session_start();

require '../includes/db.php';

/* SECURITE ADMIN */
if(!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin'){
    header("Location: ../index.php");
    exit();
}

/* VERIF ID */
if(!isset($_GET['id'])){
    header("Location: admin-commandes.php");
    exit();
}

$id = (int) $_GET['id'];

/* DELETE */
$sql = "DELETE FROM commandes WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

/* REDIRECTION */
header("Location: admin-commandes.php");
exit();