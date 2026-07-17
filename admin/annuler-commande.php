<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$fromDetail = ($_GET['from'] ?? '') === 'detail';
$pageTitle = 'Annuler commande';

$stmt = $pdo->prepare('
    SELECT c.id, c.statut, u.nom, u.prenom, u.email, u.gsm, u.telephone
    FROM commandes c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
');
$stmt->execute([$id]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    header('Location: admin-commandes.php');
    exit();
}

if ($commande['statut'] === 'annulee') {
    header('Location: ' . ($fromDetail ? 'commande-detail.php?id=' . $id : 'admin-commandes.php'));
    exit();
}

$tel = trim($commande['telephone'] ?? $commande['gsm'] ?? '');
$flashError = $_SESSION['admin_flash_error'] ?? null;
unset($_SESSION['admin_flash_error']);

require __DIR__ . '/partials/layout.php';
?>

<h1 class="h2">Refuser / annuler la commande #<?= $id ?></h1>

<?php if ($flashError): ?>
<div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<div class="alert alert-warning">
    <strong>Obligatoire :</strong> vous devez avoir contacte le client par <strong>telephone (GSM)</strong> ou <strong>email</strong>
    avant de valider cette annulation. Le mode de contact et le motif seront enregistres dans l'historique.
</div>

<div class="card-custom mb-4 col-lg-8">
    <h5 class="mb-3">Client</h5>
    <p class="mb-2"><strong><?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></strong></p>
    <p class="mb-2">Email : <a href="mailto:<?= htmlspecialchars($commande['email']) ?>"><?= htmlspecialchars($commande['email']) ?></a></p>
    <?php if ($tel): ?>
    <p class="mb-3">Telephone : <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $tel)) ?>"><?= htmlspecialchars($tel) ?></a></p>
    <?php else: ?>
    <p class="mb-3 text-muted">Aucun numero renseigne — contactez le client par email.</p>
    <?php endif; ?>
    <div class="d-flex flex-wrap gap-2">
        <?php if ($tel): ?>
        <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $tel)) ?>" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-phone me-1"></i> Appeler
        </a>
        <?php endif; ?>
        <a href="mailto:<?= htmlspecialchars($commande['email']) ?>?subject=Commande%20%23<?= $id ?>" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-envelope me-1"></i> Envoyer un email
        </a>
    </div>
</div>

<form method="POST" action="update-statut.php" class="card-custom col-lg-8">
<?= csrfField() ?>
<input type="hidden" name="id" value="<?= $id ?>">
<input type="hidden" name="statut" value="annulee">
<?php if ($fromDetail): ?><input type="hidden" name="redirect" value="detail"><?php endif; ?>
<div class="mb-3">
<label class="form-label" for="mode-contact">Mode de contact utilise</label>
<select name="mode_contact" id="mode-contact" class="form-select" required>
<option value="">Choisir...</option>
<option value="GSM">Telephone (GSM)</option>
<option value="Email">Email</option>
</select>
</div>
<div class="mb-3">
<label class="form-label" for="motif-annulation">Motif du refus / annulation</label>
<textarea name="motif" id="motif-annulation" class="form-control" rows="4" required placeholder="Ex. : delai de reservation insuffisant, date indisponible, stock insuffisant..."></textarea>
</div>
<div class="mb-3 form-check">
<input type="checkbox" class="form-check-input" name="contact_confirme" value="1" id="contact-confirme" required>
<label class="form-check-label" for="contact-confirme">Je confirme avoir contacte le client avant cette annulation</label>
</div>
<button class="btn btn-danger">Confirmer le refus</button>
<a href="<?= $fromDetail ? 'commande-detail.php?id=' . $id : 'admin-commandes.php' ?>" class="btn btn-secondary">Retour</a>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>
