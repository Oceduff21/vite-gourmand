<?php
session_start();

if(!isset($_SESSION["admin"])){
    header("Location: login.php");
    exit();
}
?>

<h1>Dashboard Admin</h1>

<ul>
    <li><a href="reviews.php">Gérer les avis</a></li>
    <li><a href="logout.php">Déconnexion</a></li>
</ul>