<?php
session_start();
require '../includes/db.php';

$id = (int)$_GET["id"];

$pdo->prepare("UPDATE avis SET is_validated = 1 WHERE id = ?")
    ->execute([$id]);

header("Location: admin-avis.php");