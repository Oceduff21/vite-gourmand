<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$fromDetail = ($_GET['from'] ?? '') === 'detail';
$pageTitle = 'Annuler commande';
require __DIR__ . '/partials/layout.php';
?>

<h2>Refuser / annuler la commande #<?= $id ?></h2>
<div class="alert alert-warning">
    <strong>Obligatoire :</strong> vous devez avoir contacte le client par <strong>telephone (GSM)</strong> ou <strong>email</strong>
    avant de valider cette annulation. Le mode de contact et le motif seront enregistres dans l'historique.
</div>
<p class="text-muted">Delai insuffisant, stock, indisponibilite date, etc.</p>

<form method="POST" action="update-statut.php" class="card-custom col-md-6">
<?= csrfField() ?>
<input type="hidden" name="id" value="<?= $id ?>">
<input type="hidden" name="statut" value="annulee">
<?php if ($fromDetail): ?><input type="hidden" name="redirect" value="detail"><?php endif; ?>
<div class="mb-3">
<label class="form-label">Mode de contact</label>
<select name="mode_contact" class="form-select" required>
<option value="">Choisir...</option>
<option value="GSM">Telephone (GSM)</option>
<option value="Email">Email</option>
</select>
</div>
<div class="mb-3">
<label class="form-label">Motif du refus / annulation</label>
<textarea name="motif" class="form-control" rows="4" required placeholder="Ex. : delai de reservation insuffisant, date indisponible..."></textarea>
</div>
<button class="btn btn-danger">Confirmer le refus</button>
<a href="<?= $fromDetail ? 'commande-detail.php?id=' . $id : 'admin-commandes.php' ?>" class="btn btn-secondary">Retour</a>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>
