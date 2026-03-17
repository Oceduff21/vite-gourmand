<?php
require 'includes/db.php';

$id = $_GET["id"];
$statut = $_GET["statut"];

$sql = "UPDATE commandes SET statut=? WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$statut, $id]);

header("Location: admin-commandes.php");