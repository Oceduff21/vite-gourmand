<?php
require 'includes/db.php';

$id = $_GET["id"];

$sql = "DELETE FROM commandes WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header("Location: admin-commandes.php");