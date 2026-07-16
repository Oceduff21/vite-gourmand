<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$pageTitle = 'Plats';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nom'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }
    $nom = trim($_POST['nom']);
    $type = $_POST['type'] ?? 'plat';
    $description = trim($_POST['description'] ?? '');
    $regime = trim($_POST['regime'] ?? 'classique');
    $allergenes = trim($_POST['allergenes'] ?? '');
    $image = 'default.jpg';

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $image = preg_replace('/[^a-z0-9._-]/', '', strtolower($_FILES['image']['name']));
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/images/' . $image);
        }
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO plats (nom, type, description, image, regime, allergenes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nom, $type, $description, $image, $regime, $allergenes]);
    } catch (Throwable $e) {
        $stmt = $pdo->prepare('INSERT INTO plats (nom, type, description, image) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nom, $type, $description, $image]);
    }
    header('Location: admin-plats.php');
    exit();
}

$plats = $pdo->query('SELECT * FROM plats ORDER BY type, nom')->fetchAll();
$regimes = ['classique', 'vegetarien', 'vegan', 'sans gluten', 'sans lactose', 'halal', 'pescetarien'];

require __DIR__ . '/partials/layout.php';
?>

<h2 class="mb-4">Gestion des plats</h2>

<div class="card-custom mb-4">
<form method="POST" enctype="multipart/form-data">
<?= csrfField() ?>
<div class="row g-3">
<div class="col-md-4"><input type="text" name="nom" placeholder="Nom" class="form-control" required></div>
<div class="col-md-2">
<select name="type" class="form-select">
<option value="entree">Entree</option>
<option value="plat">Plat</option>
<option value="dessert">Dessert</option>
</select>
</div>
<div class="col-md-2">
<select name="regime" class="form-select">
<?php foreach ($regimes as $r): ?>
<option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars(ucfirst($r)) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4"><input type="file" name="image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><textarea name="description" placeholder="Description" class="form-control" rows="2"></textarea></div>
<div class="col-md-12">
<label class="form-label small">Allergenes (separes par des virgules : gluten, lactose, oeufs...)</label>
<input type="text" name="allergenes" class="form-control" placeholder="Ex. : gluten, lactose">
</div>
<div class="col-md-12"><button class="btn btn-success">Ajouter</button></div>
</div>
</form>
</div>

<div class="card-custom">
<table class="table align-middle">
<thead class="table-light"><tr><th>Nom</th><th>Type</th><th>Regime</th><th>Allergenes</th><th></th></tr></thead>
<tbody>
<?php foreach ($plats as $p): ?>
<tr>
<td><?= htmlspecialchars($p['nom']) ?></td>
<td><?= htmlspecialchars($p['type']) ?></td>
<td><span class="badge bg-light text-dark"><?= htmlspecialchars($p['regime'] ?? 'classique') ?></span></td>
<td><div class="plat-allergenes"><?= renderAllergenesBadges($p['allergenes'] ?? null) ?></div></td>
<td>
<form method="POST" action="delete-plat.php" class="d-inline" onsubmit="return confirm('Supprimer ce plat ?')">
<?= csrfField() ?>
<input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
<button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
