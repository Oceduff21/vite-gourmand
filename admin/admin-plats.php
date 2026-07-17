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

<h1 class="h2 mb-4">Gestion des plats</h1>

<div class="card-custom mb-4">
<form method="POST" enctype="multipart/form-data">
<?= csrfField() ?>
<div class="row g-3">
<div class="col-md-4"><label class="form-label" for="plat-nom">Nom</label><input type="text" name="nom" id="plat-nom" placeholder="Nom du plat" class="form-control" required></div>
<div class="col-md-2">
<label class="form-label" for="plat-type-add">Type</label>
<select name="type" id="plat-type-add" class="form-select">
<option value="entree">Entree</option>
<option value="plat">Plat</option>
<option value="dessert">Dessert</option>
</select>
</div>
<div class="col-md-2">
<label class="form-label" for="plat-regime-add">Regime</label>
<select name="regime" id="plat-regime-add" class="form-select">
<?php foreach ($regimes as $r): ?>
<option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars(ucfirst($r)) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-4"><label class="form-label" for="plat-image-add">Image</label><input type="file" name="image" id="plat-image-add" class="form-control" accept="image/*"></div>
<div class="col-md-12"><label class="form-label" for="plat-desc-add">Description</label><textarea name="description" id="plat-desc-add" placeholder="Description" class="form-control" rows="2"></textarea></div>
<div class="col-md-12">
<label class="form-label" for="plat-allergenes-add">Allergenes (separes par des virgules : gluten, lactose, oeufs...)</label>
<input type="text" name="allergenes" id="plat-allergenes-add" class="form-control" placeholder="Ex. : gluten, lactose">
</div>
<div class="col-md-12"><button class="btn btn-success">Ajouter</button></div>
</div>
</form>
</div>

<div class="card-custom">
<table class="table align-middle">
<caption class="visually-hidden">Liste des plats</caption>
<thead class="table-light"><tr><th scope="col">Nom</th><th scope="col">Type</th><th scope="col">Regime</th><th scope="col">Allergenes</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
<tbody>
<?php foreach ($plats as $p): ?>
<tr>
<td><?= htmlspecialchars($p['nom']) ?></td>
<td><?= htmlspecialchars($p['type']) ?></td>
<td><span class="badge bg-light text-dark"><?= htmlspecialchars($p['regime'] ?? 'classique') ?></span></td>
<td><div class="plat-allergenes"><?= renderAllergenesBadges($p['allergenes'] ?? null) ?></div></td>
<td>
<a href="edit-plat.php?id=<?= (int)$p['id'] ?>" class="btn btn-primary btn-sm me-1">Modifier</a>
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
