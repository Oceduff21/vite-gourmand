<?php
session_start();
require '../includes/db.php';
require '../includes/helpers.php';

$filterStatut = $_GET['statut'] ?? '';
$filterClient = trim($_GET['client'] ?? '');

$sql = "SELECT commandes.*, users.nom, users.prenom, users.email, menus.titre
FROM commandes
JOIN users ON commandes.user_id = users.id
JOIN menus ON commandes.menu_id = menus.id
WHERE 1=1";
$params = [];

if ($filterStatut !== '') {
    $sql .= " AND commandes.statut = ?";
    $params[] = $filterStatut;
}
if ($filterClient !== '') {
    $sql .= " AND (users.nom LIKE ? OR users.prenom LIKE ? OR users.email LIKE ?)";
    $params[] = "%$filterClient%";
    $params[] = "%$filterClient%";
    $params[] = "%$filterClient%";
}
$sql .= " ORDER BY commandes.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll();
$statuts = getStatutsCommande();

require __DIR__ . '/partials/layout.php';
?>

<h2 class="mb-4">Gestion des commandes</h2>

<form method="GET" class="row g-3 mb-4">
<div class="col-md-3">
<select name="statut" class="form-select">
<option value="">Tous les statuts</option>
<?php foreach ($statuts as $k => $label): ?>
<option value="<?= $k ?>" <?= $filterStatut === $k ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4">
<input type="text" name="client" class="form-control" placeholder="Rechercher un client..." value="<?= htmlspecialchars($filterClient) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary w-100">Filtrer</button>
</div>
</form>

<div class="card-custom">
<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th>ID</th><th>Client</th><th>Menu</th><th>Personnes</th><th>Date</th><th>Statut</th><th>Changer statut</th>
</tr>
</thead>
<tbody>
<?php foreach ($commandes as $c): ?>
<tr>
<td><?= (int)$c['id'] ?></td>
<td><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
<td><?= htmlspecialchars($c['titre']) ?></td>
<td><?= (int)$c['nb_personnes'] ?></td>
<td><?= htmlspecialchars($c['date_livraison'] ?? '') ?><br><?= htmlspecialchars($c['heure_livraison'] ?? '') ?></td>
<td><span class="badge bg-<?= getStatutBadgeClass($c['statut']) ?>"><?= htmlspecialchars($statuts[$c['statut']] ?? $c['statut']) ?></span></td>
<td>
<form method="GET" action="update-statut.php" class="d-flex gap-1">
<input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
<select name="statut" class="form-select form-select-sm">
<?php foreach ($statuts as $k => $label): if ($k === 'annulee') continue; ?>
<option value="<?= $k ?>" <?= $c['statut'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
<?php endforeach; ?>
</select>
<button class="btn btn-sm btn-dark">OK</button>
</form>
<a href="annuler-commande.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-danger mt-1">Annuler (motif)</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>

