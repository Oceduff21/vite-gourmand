<?php
require 'includes/auth.php';
require 'includes/db.php';

$id = $_GET["id"];

$sql = "UPDATE users SET actif = NOT actif WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header("Location: admin-users.php");