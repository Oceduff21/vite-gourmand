<?php
if(!isset($_SESSION)) session_start();

if(!isset($_SESSION["user_id"]) || !in_array($_SESSION["user_role"] ?? '', ['admin', 'employe'])){
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f7fb;
    font-family:'Segoe UI';
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:0;
    left:0;
    height:100vh;
    width:220px;
    background:white;
    border-right:1px solid #e5e7eb;
    padding:20px;
}

.sidebar h4{
    margin-bottom:30px;
}

.sidebar a{
    display:block;
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    color:#374151;
    text-decoration:none;
}

.sidebar a.active,
.sidebar a:hover{
    background:#6366f1;
    color:white;
}

/* CONTENT */
.content{
    margin-left:240px;
    padding:30px;
}

/* CARD */
.card-custom{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

</style>

</head>

<body>

<div class="sidebar">

<h4>Admin</h4>

<a href="index.php">Dashboard</a>
<a href="admin-commandes.php">Commandes</a>
<a href="admin-menus.php">Menus</a>
<a href="admin-plats.php">Plats</a>
<a href="admin-users.php">Utilisateurs</a>
<a href="admin-avis.php">Avis</a>

<hr>

<a href="../index.php">← Retour site</a>

</div>

<div class="content">