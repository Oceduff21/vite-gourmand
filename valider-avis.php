<?php
require 'includes/db.php';

$id = $_POST["avis_id"];

$sql = "UPDATE avis SET valide=1 WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

header("Location: admin.php");