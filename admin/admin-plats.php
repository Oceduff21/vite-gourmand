<?php
require '../includes/db.php';

$plats = $pdo->query("SELECT * FROM plats")->fetchAll();
?>

<?php include 'partials/layout.php'; ?>

<h2 class="mb-4">Gestion des plats</h2>

<div class="card-custom mb-4">

<form method="POST" enctype="multipart/form-data">

<div class="row g-3">

<div class="col-md-4">
<input type="text" name="nom" placeholder="Nom" class="form-control">
</div>

<div class="col-md-4">
<select name="type" class="form-control">
<option value="entree">Entrée</option>
<option value="plat">Plat</option>
<option value="dessert">Dessert</option>
</select>
</div>

<div class="col-md-4">
<input type="file" name="image" class="form-control">
</div>

<div class="col-md-12">
<textarea name="description" placeholder="Description" class="form-control"></textarea>
</div>

<div class="col-md-12">
<button class="btn btn-success">Ajouter</button>
</div>

</div>

</form>

</div>

<div class="card-custom">

<table class="table">

<thead class="table-light">
<tr>
<th>Nom</th>
<th>Type</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($plats as $p): ?>

<tr>
<td><?= $p["nom"] ?></td>
<td><?= $p["type"] ?></td>

<td>
<a href="delete-plat.php?id=<?= $p["id"] ?>" class="btn btn-danger btn-sm">Supprimer</a>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>