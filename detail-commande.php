<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';
require 'includes/user-helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode('detail-commande.php?id=' . (int)($_GET['id'] ?? 0)));
    exit();
}

$userId = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: espace-utilisateur.php?tab=commandes');
    exit();
}

$commande = fetchCommandeForUser($pdo, $userId, $id);
if (!$commande) {
    http_response_code(404);
    $pageTitle = 'Commande introuvable';
    include 'includes/header.php';
    echo '<div class="container py-5 text-center user-space">';
    echo '<h2>Commande introuvable</h2>';
    echo '<p class="text-muted">Cette commande n\'existe pas ou ne vous appartient pas.</p>';
    echo '<a href="espace-utilisateur.php?tab=commandes" class="btn btn-primary mt-3">Mes commandes</a>';
    echo '</div>';
    include 'includes/footer.php';
    exit();
}

$details = fetchCommandePlats($pdo, $id);
$boissons = fetchCommandeBoissons($pdo, $id);
$historique = fetchCommandeHistorique($pdo, $id);
$statuts = getStatutsCommande();
$platLabels = getPlatTypeLabels();

$nbEnfants = (int)($commande['nb_enfants'] ?? 0);
$adultes = max(0, (int)$commande['nb_personnes'] - $nbEnfants);
$prixBoissons = computeBoissonsSubtotal($boissons);
$hasReview = userHasReviewForOrder($pdo, $userId, $id);
$reviewCheck = canUserReviewOrder($pdo, $userId, $id);

$platsByType = [];
foreach ($details as $d) {
    $platsByType[$d['type']][] = $d;
}

$pageTitle = 'Commande #' . $id;
$pageNoIndex = true;
include 'includes/header.php';
?>

<div class="container py-5 user-space">
    <nav aria-label="Fil d'Ariane" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="espace-utilisateur.php">Mon espace</a></li>
            <li class="breadcrumb-item"><a href="espace-utilisateur.php?tab=commandes">Mes commandes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Commande #<?= $id ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Commande #<?= $id ?></h1>
            <p class="text-muted mb-0">
                Passee le <?= date('d/m/Y a H:i', strtotime($commande['created_at'])) ?>
                &mdash; <?= htmlspecialchars($commande['menu_titre']) ?>
            </p>
        </div>
        <span class="badge bg-<?= getStatutBadgeClass($commande['statut']) ?> fs-6">
            <?= htmlspecialchars(getStatutLabel($commande['statut'])) ?>
        </span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-custom mb-4">
                <h2 class="h5 mb-3">Prestation</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Menu</div>
                        <div class="fw-semibold"><?= htmlspecialchars($commande['menu_titre']) ?></div>
                        <?php if (!empty($commande['theme'])): ?>
                        <span class="badge bg-secondary mt-1"><?= htmlspecialchars(ucfirst($commande['theme'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Nombre de personnes</div>
                        <div class="fw-semibold"><?= (int)$commande['nb_personnes'] ?> invite(s)</div>
                        <?php if ($nbEnfants > 0): ?>
                        <div class="small text-muted"><?= $adultes ?> adulte(s) + <?= $nbEnfants ?> menu(x) enfant</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Date de livraison</div>
                        <div class="fw-semibold"><?= date('d/m/Y', strtotime($commande['date_livraison'])) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Heure de livraison</div>
                        <div class="fw-semibold"><?= htmlspecialchars(substr($commande['heure_livraison'], 0, 5)) ?></div>
                    </div>
                </div>
            </div>

            <div class="card-custom mb-4">
                <h2 class="h5 mb-3">Adresse de livraison</h2>
                <p class="mb-0">
                    <?= htmlspecialchars($commande['numero'] . ' ' . $commande['rue']) ?><br>
                    <?php if (!empty($commande['complement'])): ?><?= htmlspecialchars($commande['complement']) ?><br><?php endif; ?>
                    <?= htmlspecialchars($commande['code_postal'] . ' ' . $commande['ville']) ?>
                </p>
            </div>

            <?php if ($details): ?>
            <div class="card-custom mb-4">
                <h2 class="h5 mb-3">Plats selectionnes</h2>
                <?php foreach (['entree', 'plat', 'dessert'] as $type): ?>
                    <?php if (empty($platsByType[$type])) continue; ?>
                    <h3 class="h6 text-uppercase text-muted mt-3 mb-2"><?= htmlspecialchars($platLabels[$type] ?? ucfirst($type)) ?></h3>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Plat</th>
                                    <th>Regime</th>
                                    <th class="text-end">Invites</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($platsByType[$type] as $d): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($d['nom']) ?>
                                        <?php if (!empty($d['allergenes'])): ?>
                                        <div class="plat-allergenes mt-1"><?= renderAllergenesBadges($d['allergenes'], false) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($d['regime'] ?? 'classique') ?></span></td>
                                    <td class="text-end"><?= (int)$d['quantite'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($boissons): ?>
            <div class="card-custom mb-4">
                <h2 class="h5 mb-3">Boissons</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Boisson</th>
                                <th class="text-end">Qte</th>
                                <th class="text-end">Prix unit.</th>
                                <th class="text-end">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($boissons as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['nom']) ?></td>
                                <td class="text-end"><?= (int)$b['quantite'] ?></td>
                                <td class="text-end"><?= number_format((float)$b['prix_unitaire'], 2) ?> EUR</td>
                                <td class="text-end"><?= number_format((float)$b['prix_unitaire'] * (int)$b['quantite'], 2) ?> EUR</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <div class="card-custom mb-4" id="suivi">
                <h2 class="h5 mb-3">Suivi de la commande</h2>
                <?php if ($historique): ?>
                <ul class="list-unstyled mb-0 order-timeline">
                    <?php foreach ($historique as $h): ?>
                    <li class="order-timeline-item">
                        <div class="fw-semibold"><?= htmlspecialchars($statuts[$h['statut']] ?? $h['statut']) ?></div>
                        <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></div>
                        <?php if (!empty($h['note'])): ?><div class="small mt-1"><?= htmlspecialchars($h['note']) ?></div><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted small mb-0">Le suivi detaille sera disponible des que l'equipe traitera votre commande.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-custom mb-4">
                <h2 class="h5 mb-3">Recapitulatif du montant</h2>
                <div class="d-flex justify-content-between mb-2">
                    <span>Menus (<?= $adultes ?> adulte<?= $adultes > 1 ? 's' : '' ?><?= $nbEnfants > 0 ? ', ' . $nbEnfants . ' enfant' . ($nbEnfants > 1 ? 's' : '') : '' ?>)</span>
                    <span><?= number_format((float)$commande['prix_menu'], 2) ?> EUR</span>
                </div>
                <?php if ($prixBoissons > 0): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Boissons</span>
                    <span><?= number_format($prixBoissons, 2) ?> EUR</span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Livraison</span>
                    <span><?= number_format((float)$commande['prix_livraison'], 2) ?> EUR</span>
                </div>
                <?php if ((float)$commande['reduction'] > 0): ?>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Reduction (-10%)</span>
                    <span>-<?= number_format((float)$commande['reduction'], 2) ?> EUR</span>
                </div>
                <?php endif; ?>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total paye</span>
                    <span class="text-danger"><?= number_format((float)$commande['prix_total'], 2) ?> EUR</span>
                </div>
            </div>

            <div class="card-custom">
                <h2 class="h6 mb-3">Actions</h2>
                <div class="d-grid gap-2">
                    <?php if ($commande['statut'] === 'en_attente'): ?>
                    <a href="modifier-commande.php?id=<?= $id ?>" class="btn btn-warning">Modifier (plats, livraison…)</a>
                    <form method="POST" action="annuler-commande.php" onsubmit="return confirm('Annuler cette commande ?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn btn-danger w-100">Annuler</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($reviewCheck['can_review']): ?>
                    <a href="avis.php?commande_id=<?= $id ?>" class="btn btn-success"><i class="fa-solid fa-star me-1"></i> Donner un avis</a>
                    <?php elseif ($hasReview): ?>
                    <span class="badge bg-secondary text-center py-2">Avis deja envoye</span>
                    <?php endif; ?>
                    <a href="menu.php?id=<?= (int)$commande['menu_id'] ?>" class="btn btn-outline-dark">Commander a nouveau</a>
                    <a href="espace-utilisateur.php?tab=commandes" class="btn btn-outline-secondary">Retour a mes commandes</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-timeline { border-left: 3px solid #dee2e6; margin-left: 8px; padding-left: 20px; }
.order-timeline-item { position: relative; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f0f0f0; }
.order-timeline-item:last-child { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; }
.order-timeline-item::before {
    content: '';
    position: absolute;
    left: -27px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #28a745;
}
</style>

<?php include 'includes/footer.php'; ?>
