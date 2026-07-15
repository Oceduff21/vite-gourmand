<?php
session_start();
require '../includes/db.php';

/* FILTRE */
$filter = $_GET['filter'] ?? 'all';

$where = "";
if($filter === 'pending') $where = "WHERE is_validated = 0";
if($filter === 'validated') $where = "WHERE is_validated = 1";

/* DATA */
$avis = $pdo->query("
SELECT avis.*, users.nom 
FROM avis
JOIN users ON avis.user_id = users.id
$where
ORDER BY avis.id DESC
")->fetchAll();
?>

<?php include 'partials/layout.php'; ?>

<h2 class="mb-4">Gestion des avis</h2>

<!-- FILTER -->
<div class="mb-3">

<a href="?filter=all" class="btn btn-sm <?= $filter=='all'?'btn-dark':'btn-outline-dark' ?>">Tous</a>
<a href="?filter=pending" class="btn btn-sm <?= $filter=='pending'?'btn-warning':'btn-outline-warning' ?>">En attente</a>
<a href="?filter=validated" class="btn btn-sm <?= $filter=='validated'?'btn-success':'btn-outline-success' ?>">Validés</a>

</div>

<div class="card-custom">

<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
<th>Client</th>
<th>Note</th>
<th>Commentaire</th>
<th>Statut</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($avis as $a): ?>

<tr>

<td><?= htmlspecialchars($a["nom"]) ?></td>

<td>
<?php for($i=1; $i<=5; $i++): ?>
<span style="color:<?= $i <= $a["note"] ? '#facc15' : '#e5e7eb' ?>;">★</span>
<?php endfor; ?>
</td>

<td>
<?= substr(htmlspecialchars($a["commentaire"]),0,40) ?>...
</td>

<td>
<span class="badge bg-<?= $a["is_validated"] ? 'success' : 'secondary' ?>">
<?= $a["is_validated"] ? 'Validé' : 'En attente' ?>
</span>
</td>

<td>

<!-- PREVIEW -->
<button class="btn btn-sm btn-outline-primary"
data-bs-toggle="modal"
data-bs-target="#modal<?= $a["id"] ?>">
Voir
</button>

<?php if(!$a["is_validated"]): ?>
<a href="validate-avis.php?id=<?= $a["id"] ?>" 
class="btn btn-sm btn-success">
Valider
</a>
<?php endif; ?>

<a href="delete-avis.php?id=<?= $a["id"] ?>" 
class="btn btn-sm btn-danger"
onclick="return confirm('Supprimer cet avis ?')">
Supprimer
</a>

</td>

</tr>

<!-- MODAL PREVIEW -->
<div class="modal fade" id="modal<?= $a["id"] ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Avis de <?= htmlspecialchars($a["nom"]) ?></h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<p><strong>Note :</strong></p>
<p style="font-size:20px;">
<?php for($i=1; $i<=5; $i++): ?>
<span style="color:<?= $i <= $a["note"] ? '#facc15' : '#e5e7eb' ?>;">★</span>
<?php endfor; ?>
</p>

<p><strong>Commentaire :</strong></p>
<p><?= nl2br(htmlspecialchars($a["commentaire"])) ?></p>

</div>

<div class="modal-footer">

<?php if(!$a["is_validated"]): ?>
<a href="validate-avis.php?id=<?= $a["id"] ?>" class="btn btn-success">
Valider
</a>
<?php endif; ?>

<a href="delete-avis.php?id=<?= $a["id"] ?>" class="btn btn-danger">
Supprimer
</a>

</div>

</div>
</div>
</div>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>