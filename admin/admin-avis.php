<?php

require __DIR__ . '/partials/auth.php';

requireAdminAccess();



require '../includes/db.php';



$filter = $_GET['filter'] ?? 'all';



$where = '';

if ($filter === 'pending') {

    $where = 'WHERE is_validated = 0';

}

if ($filter === 'validated') {

    $where = 'WHERE is_validated = 1';

}



$avis = $pdo->query("

SELECT avis.*, users.nom

FROM avis

JOIN users ON avis.user_id = users.id

$where

ORDER BY avis.id DESC

")->fetchAll();

?>



<?php include 'partials/layout.php'; ?>



<h1 class="h2 mb-4">Gestion des avis</h1>



<?php if ($isEmploye): ?>

<div class="alert alert-info py-2 small mb-3" role="status">

    Validez les avis pour les afficher sur la page d'accueil, ou refusez-les s'ils ne respectent pas la charte.

</div>

<?php endif; ?>



<nav class="mb-3" aria-label="Filtrer les avis">

<a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-dark' : 'btn-outline-dark' ?>" <?= $filter === 'all' ? 'aria-current="page"' : '' ?>>Tous</a>

<a href="?filter=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>" <?= $filter === 'pending' ? 'aria-current="page"' : '' ?>>En attente</a>

<a href="?filter=validated" class="btn btn-sm <?= $filter === 'validated' ? 'btn-success' : 'btn-outline-success' ?>" <?= $filter === 'validated' ? 'aria-current="page"' : '' ?>>Valides</a>

</nav>



<div class="card-custom">

<table class="table table-hover align-middle">

<caption class="visually-hidden">Liste des avis clients a moderer</caption>

<thead class="table-light">

<tr>

<th scope="col">Client</th>

<th scope="col">Note</th>

<th scope="col">Commentaire</th>

<th scope="col">Statut</th>

<th scope="col"><span class="visually-hidden">Actions</span></th>

</tr>

</thead>

<tbody>

<?php if (empty($avis)): ?>

<tr><td colspan="5" class="text-center text-muted py-4">Aucun avis.</td></tr>

<?php endif; ?>

<?php foreach ($avis as $a): ?>

<tr>

<td><?= htmlspecialchars($a['nom']) ?></td>

<td><?= renderStarRating((int)$a['note']) ?></td>

<td>

<?php

$comment = htmlspecialchars($a['commentaire']);

echo mb_strlen($comment) > 40 ? mb_substr($comment, 0, 40) . '...' : $comment;

?>

</td>

<td>

<span class="badge bg-<?= $a['is_validated'] ? 'success' : 'secondary' ?>">

<?= $a['is_validated'] ? 'Valide' : 'En attente' ?>

</span>

</td>

<td>

<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal<?= (int)$a['id'] ?>">

    Voir le detail

</button>

<?php if (!$a['is_validated']): ?>

<form method="POST" action="validate-avis.php" class="d-inline">

<?= csrfField() ?>

<input type="hidden" name="id" value="<?= (int)$a['id'] ?>">

<button type="submit" class="btn btn-sm btn-success">Valider</button>

</form>

<form method="POST" action="delete-avis.php" class="d-inline" onsubmit="return confirm('Refuser cet avis ? Il ne sera pas publie sur le site.')">

<?= csrfField() ?>

<input type="hidden" name="id" value="<?= (int)$a['id'] ?>">

<button type="submit" class="btn btn-sm btn-outline-danger">Refuser</button>

</form>

<?php else: ?>

<form method="POST" action="delete-avis.php" class="d-inline" onsubmit="return confirm('Supprimer cet avis valide ?')">

<?= csrfField() ?>

<input type="hidden" name="id" value="<?= (int)$a['id'] ?>">

<button type="submit" class="btn btn-sm btn-danger">Supprimer</button>

</form>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>



<?php foreach ($avis as $a): ?>

<div class="modal fade" id="modal<?= (int)$a['id'] ?>" tabindex="-1" aria-labelledby="modal-title-<?= (int)$a['id'] ?>" aria-hidden="true">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h3 class="modal-title h5" id="modal-title-<?= (int)$a['id'] ?>">Avis de <?= htmlspecialchars($a['nom']) ?></h3>

<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>

</div>

<div class="modal-body">

<p class="mb-2"><strong>Note :</strong></p>

<div class="mb-3"><?= renderStarRating((int)$a['note']) ?></div>

<p class="mb-1"><strong>Commentaire :</strong></p>

<p><?= nl2br(htmlspecialchars($a['commentaire'])) ?></p>

</div>

<div class="modal-footer">

<?php if (!$a['is_validated']): ?>

<form method="POST" action="validate-avis.php" class="d-inline">

<?= csrfField() ?>

<input type="hidden" name="id" value="<?= (int)$a['id'] ?>">

<button type="submit" class="btn btn-success">Valider</button>

</form>

<form method="POST" action="delete-avis.php" class="d-inline" onsubmit="return confirm('Refuser cet avis ?')">

<?= csrfField() ?>

<input type="hidden" name="id" value="<?= (int)$a['id'] ?>">

<button type="submit" class="btn btn-outline-danger">Refuser</button>

</form>

<?php else: ?>

<form method="POST" action="delete-avis.php" class="d-inline" onsubmit="return confirm('Supprimer cet avis ?')">

<?= csrfField() ?>

<input type="hidden" name="id" value="<?= (int)$a['id'] ?>">

<button type="submit" class="btn btn-danger">Supprimer</button>

</form>

<?php endif; ?>

</div>

</div>

</div>

</div>

<?php endforeach; ?>



<?php require __DIR__ . '/partials/footer.php'; ?>

