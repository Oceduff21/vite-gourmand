<?php
session_start();
require 'includes/db.php';

if($_SESSION["user_role"] !== "admin"){
die("Accès refusé");
}

$sql = "SELECT * FROM menus";
$stmt = $pdo->query($sql);
$menus = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<h1>Gestion des menus</h1>

<a href="create-menu.php" class="btn btn-success mb-3">
Ajouter un menu
</a>

<table class="table">

<thead>

<tr>
<th>Titre</th>
<th>Prix</th>
<th>Personnes</th>
<th>Actions</th>
</tr>

</thead>

<tbody>

<?php foreach($menus as $menu): ?>

<tr>

<td><?= $menu["titre"] ?></td>

<td><?= $menu["prix"] ?> €</td>

<td><?= $menu["min_personnes"] ?></td>

<td>

<a href="edit-menu.php?id=<?= $menu["id"] ?>"
class="btn btn-primary btn-sm">
Modifier
</a>

<a href="delete-menu.php?id=<?= $menu["id"] ?>"
class="btn btn-danger btn-sm">
Supprimer
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include 'includes/footer.php'; ?>