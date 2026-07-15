<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit();
}

$id = $_GET["id"];

$stmt = $pdo->prepare("
UPDATE commandes 
SET statut='annulee' 
WHERE id=? AND user_id=? AND statut='en_attente'
");

$stmt->execute([$id, $_SESSION["user_id"]]);

header("Location: espace-utilisateur.php");
exit();