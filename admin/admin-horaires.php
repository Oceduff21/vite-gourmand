<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';
require '../includes/site-settings.php';

$pageTitle = 'Horaires';
$message = '';
$error = '';
$horaires = getHoraires($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Session expiree. Rechargez la page.';
    } else {
        $lv = trim($_POST['horaire_lv'] ?? '');
        $samedi = trim($_POST['horaire_samedi'] ?? '');
        $dimanche = trim($_POST['horaire_dimanche'] ?? '');

        if ($lv === '' || $samedi === '' || $dimanche === '') {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            setSiteSetting($pdo, 'horaire_lv', $lv);
            setSiteSetting($pdo, 'horaire_samedi', $samedi);
            setSiteSetting($pdo, 'horaire_dimanche', $dimanche);
            $horaires = getHoraires($pdo);
            $message = 'Horaires mis a jour. Ils apparaissent dans le pied de page du site.';
        }
    }
}

require __DIR__ . '/partials/layout.php';
?>

<h1 class="h2 mb-4">Horaires d'ouverture</h1>

<p class="text-muted mb-4">Ces horaires sont affiches dans le pied de page du site public (section « Horaires »).</p>

<?php if ($message): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card-custom">
<form method="POST" class="row g-3">
<?= csrfField() ?>
<div class="col-md-12">
    <label class="form-label small fw-semibold" for="horaire-lv">Lundi - Vendredi</label>
    <input type="text" name="horaire_lv" id="horaire-lv" class="form-control" value="<?= htmlspecialchars($horaires['horaire_lv']) ?>" required>
</div>
<div class="col-md-12">
    <label class="form-label small fw-semibold" for="horaire-samedi">Samedi</label>
    <input type="text" name="horaire_samedi" id="horaire-samedi" class="form-control" value="<?= htmlspecialchars($horaires['horaire_samedi']) ?>" required>
</div>
<div class="col-md-12">
    <label class="form-label small fw-semibold" for="horaire-dimanche">Dimanche</label>
    <input type="text" name="horaire_dimanche" id="horaire-dimanche" class="form-control" value="<?= htmlspecialchars($horaires['horaire_dimanche']) ?>" required>
</div>
<div class="col-12">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-clock me-1"></i> Enregistrer les horaires</button>
</div>
</form>
</div>

<div class="card-custom mt-4">
<h5 class="mb-3">Apercu</h5>
<?php foreach (array_values($horaires) as $ligne): ?>
<p class="mb-1"><?= htmlspecialchars($ligne) ?></p>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
