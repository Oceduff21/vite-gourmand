<?php
require 'includes/db.php';

/* RECUP COMMANDES + USERS + MENUS */
$sql = "
SELECT commandes.*, users.nom, users.email, menus.titre
FROM commandes
JOIN users ON commandes.user_id = users.id
JOIN menus ON commandes.menu_id = menus.id
ORDER BY commandes.id DESC
";

$stmt = $pdo->query($sql);
$commandes = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">

<h2 class="mb-4">Gestion des commandes</h2>

<table class="table table-bordered table-hover">

<thead class="table-dark">
<tr>
<th>ID</th>
<th>Client</th>
<th>Email</th>
<th>Menu</th>
<th>Date</th>
<th>Statut</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($commandes as $c): ?>

<tr>

<td><?= $c["id"] ?></td>

<td><?= htmlspecialchars($c["nom"]) ?></td>
<td><?= htmlspecialchars($c["email"]) ?></td>

<td><?= htmlspecialchars($c["titre"]) ?></td>

<td><?= $c["date_commande"] ?></td>

<td>
<span class="badge bg-<?=
$c["statut"] == "validee" ? "success" :
($c["statut"] == "annulee" ? "danger" : "warning")
?>">
<?= $c["statut"] ?>
</span>
</td>

<td>

<a href="update-statut.php?id=<?= $c["id"] ?>&statut=validee" class="btn btn-success btn-sm">
Valider
</a>

<a href="update-statut.php?id=<?= $c["id"] ?>&statut=annulee" class="btn btn-danger btn-sm">
Annuler
</a>

<a href="delete-commande.php?id=<?= $c["id"] ?>" class="btn btn-dark btn-sm">
Supprimer
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include 'includes/footer.php'; ?>