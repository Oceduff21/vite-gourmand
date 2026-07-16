<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}
require '../includes/db.php';

$menus = $pdo->query("SELECT * FROM menus")->fetchAll();
?>

<?php include 'partials/layout.php'; ?>

<h2 class="mb-4">Gestion des menus</h2>

<a href="add-menu.php" class="btn btn-success mb-3">+ Ajouter un menu</a>

<div class="card-custom">

<table class="table table-hover">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Titre</th>
<th>Prix</th>
<th>Min</th>
<th>Stock</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($menus as $m): ?>

<tr>
<td><?= $m["id"] ?></td>
<td><?= $m["titre"] ?></td>
<td><?= $m["prix"] ?> €</td>
<td><?= $m["min_personnes"] ?></td>
<td><?= $m["stock"] ?></td>

<td>
<a href="edit-menu.php?id=<?= $m["id"] ?>" class="btn btn-sm btn-primary">Modifier</a>
<a href="delete-menu.php?id=<?= $m["id"] ?>" class="btn btn-sm btn-danger">Supprimer</a>
</td>
</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
