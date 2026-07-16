<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'employe'])) {
    header('Location: ../index.php');
    exit();
}
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nom'])) {
    $nom = trim($_POST['nom']);
    $type = $_POST['type'] ?? 'plat';
    $description = trim($_POST['description'] ?? '');
    $image = 'default.jpg';

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $image = preg_replace('/[^a-z0-9._-]/', '', strtolower($_FILES['image']['name']));
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/images/' . $image);
        }
    }

    $stmt = $pdo->prepare('INSERT INTO plats (nom, type, description, image) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nom, $type, $description, $image]);
    header('Location: admin-plats.php');
    exit();
}

$plats = $pdo->query('SELECT * FROM plats')->fetchAll();

require __DIR__ . '/partials/layout.php';
?>

<h2 class="mb-4">Gestion des plats</h2>

<div class="card-custom mb-4">
<form method="POST" enctype="multipart/form-data">
<div class="row g-3">
<div class="col-md-4"><input type="text" name="nom" placeholder="Nom" class="form-control" required></div>
<div class="col-md-4">
<select name="type" class="form-control">
<option value="entree">Entree</option>
<option value="plat">Plat</option>
<option value="dessert">Dessert</option>
</select>
</div>
<div class="col-md-4"><input type="file" name="image" class="form-control" accept="image/*"></div>
<div class="col-md-12"><textarea name="description" placeholder="Description" class="form-control"></textarea></div>
<div class="col-md-12"><button class="btn btn-success">Ajouter</button></div>
</div>
</form>
</div>

<div class="card-custom">
<table class="table">
<thead class="table-light"><tr><th>Nom</th><th>Type</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($plats as $p): ?>
<tr>
<td><?= htmlspecialchars($p['nom']) ?></td>
<td><?= htmlspecialchars($p['type']) ?></td>
<td>
<a href="delete-plat.php?id=<?= (int)$p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce plat ?')">Supprimer</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
