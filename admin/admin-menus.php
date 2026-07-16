<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}
require '../includes/db.php';

$menus = $pdo->query('
    SELECT m.*,
        (SELECT COUNT(*) FROM menu_options mo WHERE mo.menu_id = m.id AND mo.type = "entree") AS nb_entrees,
        (SELECT COUNT(*) FROM menu_options mo WHERE mo.menu_id = m.id AND mo.type = "plat") AS nb_plats,
        (SELECT COUNT(*) FROM menu_options mo WHERE mo.menu_id = m.id AND mo.type = "dessert") AS nb_desserts
    FROM menus m ORDER BY m.titre
')->fetchAll();
?>

<?php include 'partials/layout.php'; ?>

<h2 class="mb-4">Gestion des menus</h2>

<a href="add-menu.php" class="btn btn-success mb-3">+ Ajouter un menu</a>

<div class="card-custom">

<table class="table table-hover">

<thead class="table-light">
<tr>
<th>Titre</th>
<th>Theme</th>
<th>Prix</th>
<th>Min</th>
<th>Plats (E/P/D)</th>
<th>Stock</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($menus as $m): ?>

<tr>
<td><?= htmlspecialchars($m['titre']) ?></td>
<td><?= htmlspecialchars($m['theme'] ?? '-') ?></td>
<td><?= number_format((float)$m['prix'], 2) ?> &euro;</td>
<td><?= (int)$m['min_personnes'] ?></td>
<td>
<?php
$ok = (int)$m['nb_entrees'] === 3 && (int)$m['nb_plats'] === 3 && (int)$m['nb_desserts'] === 3;
?>
<span class="badge bg-<?= $ok ? 'success' : 'warning text-dark' ?>">
<?= (int)$m['nb_entrees'] ?>/<?= (int)$m['nb_plats'] ?>/<?= (int)$m['nb_desserts'] ?>
</span>
</td>
<td><?= (int)$m['stock'] ?></td>

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
