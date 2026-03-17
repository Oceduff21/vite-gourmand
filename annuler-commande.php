<?php
session_start();
require 'includes/db.php';

if(!isset($_SESSION["user_id"])){
header("Location: login.php");
exit();
}

$id = $_GET["id"];

/* vérifier que la commande appartient à l'utilisateur */

$sql = "DELETE FROM commandes
WHERE id=? AND user_id=? AND statut='en attente'";

$stmt = $pdo->prepare($sql);

$stmt->execute([
$id,
$_SESSION["user_id"]
]);

header("Location: espace-utilisateur.php");
exit();