<?php
session_start();
require '../includes/db.php';
require '../includes/helpers.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}

$id = (int)($_GET['id'] ?? 0);
require __DIR__ . '/partials/layout.php';
?>

<h2>Annuler la commande #<?= $id ?></h2>
<p class="text-muted">Contactez le client par GSM ou email avant d'annuler (CDC).</p>

<form method="POST" action="update-statut.php" class="card-custom col-md-6">
<input type="hidden" name="id" value="<?= $id ?>">
<input type="hidden" name="statut" value="annulee">
<div class="mb-3">
<label class="form-label">Mode de contact</label>
<select name="mode_contact" class="form-select" required>
<option value="">Choisir...</option>
<option value="GSM">Telephone (GSM)</option>
<option value="Email">Email</option>
</select>
</div>
<div class="mb-3">
<label class="form-label">Motif d'annulation</label>
<textarea name="motif" class="form-control" rows="4" required></textarea>
</div>
<button class="btn btn-danger">Confirmer l'annulation</button>
<a href="admin-commandes.php" class="btn btn-secondary">Retour</a>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>
