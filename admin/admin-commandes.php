<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$pageTitle = 'Commandes';
$filterStatut = $_GET['statut'] ?? '';
$filterClient = trim($_GET['client'] ?? '');

$sql = "SELECT commandes.*, users.nom, users.prenom, users.email, menus.titre, menus.delai_jours
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
$sql .= " ORDER BY
    CASE WHEN commandes.statut = 'en_attente' THEN 0 ELSE 1 END,
    commandes.date_livraison ASC,
    commandes.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$statuts = getStatutsCommande();

$nbEnAttente = 0;
foreach ($commandes as $row) {
    if ($row['statut'] === 'en_attente') {
        $nbEnAttente++;
    }
}

require __DIR__ . '/partials/layout.php';
?>

<h2 class="mb-4">Gestion des commandes</h2>

<?php if ($isEmploye): ?>
<div class="alert alert-warning py-2 small">
    <strong>Procedure employe :</strong> avant tout refus ou annulation, contactez le client par <strong>GSM</strong> ou <strong>email</strong>,
    puis saisissez le mode de contact et le motif sur le formulaire dedie.
</div>
<?php endif; ?>

<?php if ($nbEnAttente > 0 && $filterStatut !== 'en_attente'): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span><strong><?= $nbEnAttente ?></strong> commande(s) en attente de validation.</span>
    <a href="?statut=en_attente" class="btn btn-sm btn-primary">Voir les commandes a traiter</a>
</div>
<?php endif; ?>

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
<?php if ($filterStatut || $filterClient): ?>
<div class="col-md-2">
<a href="admin-commandes.php" class="btn btn-outline-secondary w-100">Reinitialiser</a>
</div>
<?php endif; ?>
</form>

<div class="card-custom">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead class="table-light">
<tr>
<th>ID</th>
<th>Client</th>
<th>Menu</th>
<th>Personnes</th>
<th>Livraison</th>
<th>Delai</th>
<?php if ($canViewFinancials): ?><th>Total</th><?php endif; ?>
<th>Statut</th>
<th></th>
</tr>
</thead>
<tbody>
<?php if (empty($commandes)): ?>
<tr><td colspan="<?= $canViewFinancials ? 9 : 8 ?>" class="text-center text-muted py-4">Aucune commande trouvee.</td></tr>
<?php endif; ?>
<?php foreach ($commandes as $c):
    $delai = analyseDelaiLivraison(
        $c['date_livraison'],
        (int)($c['delai_jours'] ?? 7),
        $c['created_at'] ?? null
    );
    $nbEnfants = (int)($c['nb_enfants'] ?? 0);
?>
<tr class="<?= $c['statut'] === 'en_attente' ? 'table-warning' : '' ?>">
<td><strong>#<?= (int)$c['id'] ?></strong></td>
<td>
    <div><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></div>
    <div class="small text-muted"><?= htmlspecialchars($c['email']) ?></div>
</td>
<td><?= htmlspecialchars($c['titre']) ?></td>
<td>
    <?= (int)$c['nb_personnes'] ?>
    <?php if ($nbEnfants > 0): ?><br><span class="small text-muted"><?= $nbEnfants ?> enfant(s)</span><?php endif; ?>
</td>
<td>
    <?= date('d/m/Y', strtotime($c['date_livraison'])) ?><br>
    <span class="small text-muted"><?= substr($c['heure_livraison'], 0, 5) ?></span>
</td>
<td>
    <span class="badge bg-<?= getDelaiBadgeClass($delai) ?>"><?= htmlspecialchars(getDelaiBadgeLabel($delai)) ?></span>
    <?php if (!$delai['delai_respecte'] && $c['statut'] === 'en_attente'): ?>
    <div class="small text-danger mt-1">Min. <?= $delai['date_min_livraison'] ?></div>
    <?php endif; ?>
</td>
<?php if ($canViewFinancials): ?>
<td><?= number_format((float)$c['prix_total'], 2) ?> EUR</td>
<?php endif; ?>
<td><span class="badge bg-<?= getStatutBadgeClass($c['statut']) ?>"><?= htmlspecialchars($statuts[$c['statut']] ?? $c['statut']) ?></span></td>
<td class="text-end">
    <a href="commande-detail.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-primary">
        <?= $c['statut'] === 'en_attente' ? 'Traiter' : 'Detail' ?>
    </a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
