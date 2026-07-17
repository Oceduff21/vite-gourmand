<?php
session_start();
require 'includes/db.php';
require 'includes/helpers.php';
require 'includes/user-helpers.php';

$pageTitle = 'Avis clients — Vite & Gourmand';
$pageDescription = 'Decouvrez les avis de nos clients sur nos prestations traiteur a Bordeaux. Note moyenne et temoignages verifies.';

$commande_id = (int)($_GET['commande_id'] ?? $_POST['commande_id'] ?? 0);
$message = null;
$messageType = 'success';
$reviewStatus = null;
$pendingReviews = [];

$moyenne = 0.0;
$nbAvis = 0;
$avisPublics = [];

try {
    $moyenne = (float)$pdo->query('SELECT AVG(note) FROM avis WHERE is_validated = 1')->fetchColumn();
    $nbAvis = (int)$pdo->query('SELECT COUNT(*) FROM avis WHERE is_validated = 1')->fetchColumn();
    $stmt = $pdo->query('
        SELECT avis.note, avis.commentaire, avis.created_at, users.prenom, users.nom
        FROM avis
        JOIN users ON avis.user_id = users.id
        WHERE avis.is_validated = 1
        ORDER BY avis.created_at DESC
    ');
    $avisPublics = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $avisPublics = [];
}

if (isset($_SESSION['user_id'])) {
    $pendingReviews = getOrdersPendingReview($pdo, (int)$_SESSION['user_id']);
}

if ($commande_id > 0 && isset($_SESSION['user_id'])) {
    $reviewStatus = canUserReviewOrder($pdo, (int)$_SESSION['user_id'], $commande_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=' . urlencode('avis.php?commande_id=' . $commande_id));
        exit();
    }

    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $message = 'Session expiree. Rechargez la page.';
        $messageType = 'danger';
    } else {
        $commande_id = (int)($_POST['commande_id'] ?? 0);
        $reviewStatus = canUserReviewOrder($pdo, (int)$_SESSION['user_id'], $commande_id);
        $note = (int)($_POST['note'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');

        if (!$reviewStatus['can_review']) {
            $message = $reviewStatus['reason'] ?: 'Impossible de deposer un avis pour cette commande.';
            $messageType = 'warning';
        } elseif ($note >= 1 && $note <= 5 && $commentaire !== '') {
            $stmt = $pdo->prepare('
                INSERT INTO avis (user_id, commande_id, note, commentaire)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([(int)$_SESSION['user_id'], $commande_id, $note, $commentaire]);
            $message = 'Merci ! Votre avis sera publie apres validation par notre equipe.';
            $messageType = 'success';
            $commande_id = 0;
            $reviewStatus = null;
            $pendingReviews = getOrdersPendingReview($pdo, (int)$_SESSION['user_id']);
        } else {
            $message = 'Veuillez remplir tous les champs correctement.';
            $messageType = 'warning';
        }
    }
}

$showForm = $reviewStatus && $reviewStatus['can_review'];

include 'includes/header.php';
?>

<div class="container py-5" id="main-content">

<div class="text-center mb-5">
    <h1 class="mb-2">Avis clients</h1>
    <p class="text-muted mb-3">Ce que disent nos clients apres leurs evenements</p>
    <?php if ($nbAvis > 0): ?>
    <div class="d-inline-flex align-items-center gap-2 bg-light rounded-pill px-4 py-2" role="status">
        <span class="visually-hidden">Note moyenne :</span>
        <span class="fs-3 fw-bold text-warning" aria-hidden="true"><?= number_format($moyenne, 1) ?></span>
        <span><?= number_format($moyenne, 1) ?> / 5 — <?= $nbAvis ?> avis verifie(s)</span>
    </div>
    <?php endif; ?>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= htmlspecialchars($messageType) ?> col-lg-8 mx-auto" role="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (!empty($pendingReviews) && !$showForm): ?>
<div class="card-custom col-lg-8 mx-auto mb-5 p-4">
    <h2 class="h5 mb-3"><i class="fa-solid fa-pen-to-square me-2 text-success"></i>Vos commandes a evaluer</h2>
    <p class="text-muted small">Partagez votre experience apres la livraison de votre menu.</p>
    <ul class="list-group list-group-flush">
        <?php foreach ($pendingReviews as $pr): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2 px-0">
            <div>
                <strong>Commande #<?= (int)$pr['id'] ?></strong> — <?= htmlspecialchars($pr['menu_titre']) ?>
                <div class="small text-muted">Livree le <?= date('d/m/Y', strtotime($pr['date_livraison'])) ?></div>
            </div>
            <a href="avis.php?commande_id=<?= (int)$pr['id'] ?>" class="btn btn-success btn-sm">Laisser un avis</a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($showForm): ?>
<div class="card-custom col-lg-8 mx-auto mb-5 p-4">
    <h2 class="h5 mb-1">Donner votre avis</h2>
    <p class="text-muted small mb-3">
        Commande #<?= $commande_id ?>
        — <?= htmlspecialchars($reviewStatus['order']['menu_titre'] ?? '') ?>
        (<?= date('d/m/Y', strtotime($reviewStatus['order']['date_livraison'])) ?>)
    </p>
    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="commande_id" value="<?= $commande_id ?>">
        <div class="mb-3">
            <label class="form-label" for="avis-note">Note</label>
            <select name="note" id="avis-note" class="form-select" required>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>"><?= $i ?> etoile(s) sur 5</option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="avis-commentaire">Votre commentaire</label>
            <textarea name="commentaire" id="avis-commentaire" class="form-control" rows="4" required placeholder="Qualite des plats, ponctualite, service..."></textarea>
        </div>
        <button class="btn btn-success">Envoyer mon avis</button>
        <a href="avis.php" class="btn btn-outline-secondary ms-2">Annuler</a>
    </form>
</div>
<?php elseif ($commande_id > 0 && !isset($_SESSION['user_id'])): ?>
<div class="alert alert-info col-lg-8 mx-auto text-center">
    <a href="login.php?redirect=<?= urlencode('avis.php?commande_id=' . $commande_id) ?>">Connectez-vous</a> pour laisser un avis sur votre commande.
</div>
<?php elseif ($commande_id > 0 && $reviewStatus && !$reviewStatus['can_review']): ?>
<div class="alert alert-warning col-lg-8 mx-auto">
    <?= htmlspecialchars($reviewStatus['reason']) ?>
    <?php if ($reviewStatus['already_reviewed']): ?>
    <a href="avis.php" class="d-block mt-2">Voir les avis publies</a>
    <?php else: ?>
    <a href="espace-utilisateur.php?tab=commandes" class="d-block mt-2">Retour a mes commandes</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (empty($avisPublics)): ?>
<div class="text-center text-muted py-5">
    <p class="mb-0">Les premiers avis seront bientot disponibles.</p>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($avisPublics as $a):
        $initiale = strtoupper(substr($a['prenom'] ?? $a['nom'] ?? 'C', 0, 1));
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="review-card h-100 p-4 bg-white rounded-4 shadow-sm">
            <div class="mb-2">
                <?= renderStarRating((int)$a['note']) ?>
            </div>
            <blockquote class="mb-3"><p class="mb-0">« <?= htmlspecialchars($a['commentaire']) ?> »</p></blockquote>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;font-weight:700"><?= htmlspecialchars($initiale) ?></span>
                <div>
                    <div class="fw-semibold small"><?= htmlspecialchars(trim(($a['prenom'] ?? '') . ' ' . substr($a['nom'] ?? '', 0, 1) . '.')) ?></div>
                    <div class="text-muted small"><?= date('d/m/Y', strtotime($a['created_at'])) ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
