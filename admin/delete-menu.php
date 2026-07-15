<?php
require '../includes/db.php';

if(isset($_GET['id'])){
    $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: admin-menus.php");
exit();