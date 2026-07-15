<?php
session_start();
require '../includes/db.php';

$id = (int)$_GET["id"];

$pdo->prepare("DELETE FROM avis WHERE id = ?")
    ->execute([$id]);

header("Location: admin-avis.php");