<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/menu-helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: espace-utilisateur.php?tab=commandes');
    exit();
}

$stmt = $pdo->prepare('
    SELECT c.*, m.titre AS menu_titre, m.theme
    FROM commandes c
    JOIN menus m ON m.id = c.menu_id
    WHERE c.id = ? AND c.user_id = ?
');
$stmt->execute([$id, $_SESSION['user_id']]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Commande confirmee #' . $id;
$pageNoIndex = true;

include 'includes/header.php';

if (!$commande) {
    echo '<div class="container py-5 text-center">';
    echo '<h2>Commande introuvable</h2>';
    echo '<a href="espace-utilisateur.php?tab=commandes" class="btn btn-primary mt-3">Mes commandes</a>';
    echo '</div>';
    include 'includes/footer.php';
    exit();
}

$nbEnfants = (int)($commande['nb_enfants'] ?? 0);
$adultes = max(0, (int)$commande['nb_personnes'] - $nbEnfants);

$details = [];
$stmtD = $pdo->prepare('
    SELECT cd.quantite, cd.type, p.nom
    FROM commande_details cd
    JOIN plats p ON p.id = cd.plat_id
    WHERE cd.commande_id = ?
    ORDER BY FIELD(cd.type, "entree", "plat", "dessert"), p.nom
');
$stmtD->execute([$id]);
$details = $stmtD->fetchAll(PDO::FETCH_ASSOC);

$boissons = [];
try {
    $stmtB = $pdo->prepare('
        SELECT cb.quantite, b.nom
        FROM commande_boissons cb
        JOIN boissons b ON b.id = cb.boisson_id
        WHERE cb.commande_id = ?
    ');
    $stmtB->execute([$id]);
    $boissons = $stmtB->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $boissons = [];
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="display-4 text-success mb-2"><i class="fa-solid fa-circle-check"></i></div>
                <h1 class="h2 mb-2">Commande confirmee</h1>
                <p class="text-muted mb-0">Merci <?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?> ! Votre demande est enregistree.</p>
            </div>

            <div class="card-custom mb-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <span class="text-muted small">Commande</span>
                        <h2 class="h4 mb-0">#<?= $id ?></h2>
                    </div>
                    <span class="badge bg-<?= getStatutBadgeClass($commande['statut']) ?> fs-6">
                        <?= htmlspecialchars(getStatutLabel($commande['statut'])) ?>
                    </span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Menu</div>
                        <div class="fw-semibold"><?= htmlspecialchars($commande['menu_titre']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Effectif</div>
                        <div class="fw-semibold">
                            <?= (int)$commande['nb_personnes'] ?> invite(s)
                            <?php if ($nbEnfants > 0): ?>
                            <span class="text-muted small">(<?= $adultes ?> adulte(s), <?= $nbEnfants ?> enfant(s))</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Livraison</div>
                        <div class="fw-semibold">
                            <?= date('d/m/Y', strtotime($commande['date_livraison'])) ?>
                            a <?= substr($commande['heure_livraison'], 0, 5) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Total</div>
                        <div class="fw-bold text-danger fs-5"><?= number_format((float)$commande['prix_total'], 2) ?> EUR</div>
                    </div>
                </div>

                <div class="small text-muted mb-1">Adresse</div>
                <p class="mb-0">
                    <?= htmlspecialchars($commande['numero'] . ' ' . $commande['rue']) ?><br>
                    <?php if (!empty($commande['complement'])): ?><?= htmlspecialchars($commande['complement']) ?><br><?php endif; ?>
                    <?= htmlspecialchars($commande['code_postal'] . ' ' . $commande['ville']) ?>
                </p>
            </div>

            <?php if ($details): ?>
            <div class="card-custom mb-4">
                <h3 class="h6 mb-3">Votre selection</h3>
                <ul class="list-unstyled mb-0 small">
                <?php foreach ($details as $d): ?>
                    <li class="mb-1">
                        <strong><?= htmlspecialchars(ucfirst($d['type'])) ?></strong> :
                        <?= htmlspecialchars($d['nom']) ?>
                        <span class="text-muted">(<?= (int)$d['quantite'] ?> pers.)</span>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($boissons): ?>
            <div class="card-custom mb-4">
                <h3 class="h6 mb-3">Boissons</h3>
                <ul class="list-unstyled mb-0 small">
                <?php foreach ($boissons as $b): ?>
                    <li><?= htmlspecialchars($b['nom']) ?> &times; <?= (int)$b['quantite'] ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="alert alert-info">
                <strong>Prochaine etape :</strong> notre equipe va valider votre commande sous 48h.
                Vous recevrez une notification et pourrez suivre l'avancement en temps reel.
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="detail-commande.php?id=<?= $id ?>" class="btn btn-primary">Voir le detail de ma commande</a>
                <a href="detail-commande.php?id=<?= $id ?>#suivi" class="btn btn-outline-primary">Suivre ma commande</a>
                <a href="espace-utilisateur.php?tab=commandes" class="btn btn-outline-dark">Mes commandes</a>
                <a href="menus.php" class="btn btn-outline-secondary">Continuer mes achats</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
