<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: admin-plats.php');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM plats WHERE id = ?');
$stmt->execute([$id]);
$plat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plat) {
    header('Location: admin-plats.php');
    exit();
}

$pageTitle = 'Modifier un plat';
$error = '';
$regimes = ['classique', 'vegetarien', 'vegan', 'sans gluten', 'sans lactose', 'halal', 'pescetarien'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Session expiree. Rechargez la page.';
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $type = $_POST['type'] ?? 'plat';
        $description = trim($_POST['description'] ?? '');
        $regime = trim($_POST['regime'] ?? 'classique');
        $allergenes = trim($_POST['allergenes'] ?? '');
        $image = $plat['image'] ?? 'default.jpg';

        if ($nom === '') {
            $error = 'Le nom est obligatoire.';
        } else {
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $image = preg_replace('/[^a-z0-9._-]/', '', strtolower($_FILES['image']['name']));
                    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/images/' . $image);
                }
            }

            try {
                $stmt = $pdo->prepare(
                    'UPDATE plats SET nom = ?, type = ?, description = ?, image = ?, regime = ?, allergenes = ? WHERE id = ?'
                );
                $stmt->execute([$nom, $type, $description, $image, $regime, $allergenes, $id]);
            } catch (Throwable $e) {
                $stmt = $pdo->prepare('UPDATE plats SET nom = ?, type = ?, description = ?, image = ? WHERE id = ?');
                $stmt->execute([$nom, $type, $description, $image, $id]);
            }

            header('Location: admin-plats.php');
            exit();
        }
    }
}

require __DIR__ . '/partials/layout.php';
?>

<h1 class="h2 mb-4">Modifier le plat</h1>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card-custom">
<form method="POST" enctype="multipart/form-data" class="row g-3">
<?= csrfField() ?>
<div class="col-md-6">
    <label class="form-label small fw-semibold" for="plat-nom">Nom</label>
    <input type="text" name="nom" id="plat-nom" class="form-control" value="<?= htmlspecialchars($_POST['nom'] ?? $plat['nom']) ?>" required>
</div>
<div class="col-md-3">
    <label class="form-label small fw-semibold" for="plat-type">Type</label>
    <select name="type" id="plat-type" class="form-select">
        <?php foreach (['entree' => 'Entree', 'plat' => 'Plat', 'dessert' => 'Dessert'] as $val => $label): ?>
        <option value="<?= $val ?>" <?= ($plat['type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-3">
    <label class="form-label small fw-semibold" for="plat-regime">Regime</label>
    <select name="regime" id="plat-regime" class="form-select">
        <?php foreach ($regimes as $r): ?>
        <option value="<?= htmlspecialchars($r) ?>" <?= ($plat['regime'] ?? 'classique') === $r ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($r)) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-6">
    <label class="form-label small fw-semibold" for="plat-image">Image (laisser vide pour conserver l'actuelle)</label>
    <input type="file" name="image" id="plat-image" class="form-control" accept="image/*">
    <?php if (!empty($plat['image']) && $plat['image'] !== 'default.jpg'): ?>
    <div class="form-text">Image actuelle : <?= htmlspecialchars($plat['image']) ?></div>
    <?php endif; ?>
</div>
<div class="col-md-12">
    <label class="form-label small fw-semibold" for="plat-description">Description</label>
    <textarea name="description" id="plat-description" class="form-control" rows="3"><?= htmlspecialchars($plat['description'] ?? '') ?></textarea>
</div>
<div class="col-md-12">
    <label class="form-label small fw-semibold" for="plat-allergenes">Allergenes (separes par des virgules)</label>
    <input type="text" name="allergenes" id="plat-allergenes" class="form-control" value="<?= htmlspecialchars($plat['allergenes'] ?? '') ?>" placeholder="Ex. : gluten, lactose">
</div>
<div class="col-12 d-flex gap-2 flex-wrap">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Enregistrer</button>
    <a href="admin-plats.php" class="btn btn-outline-secondary">Annuler</a>
</div>
</form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
