<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';

$pageTitle = 'Boissons';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF invalide.';
    } else {
        $action = $_POST['action'] ?? 'add';
        if ($action === 'add') {
            $nom = trim($_POST['nom'] ?? '');
            $prix = (float)($_POST['prix'] ?? 0);
            $categorie = trim($_POST['categorie'] ?? 'soft');
            $description = trim($_POST['description'] ?? '');
            if ($nom === '') {
                $error = 'Le nom est obligatoire.';
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO boissons (nom, categorie, prix, description) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$nom, $categorie, $prix, $description]);
                } catch (Throwable $e) {
                    $stmt = $pdo->prepare('INSERT INTO boissons (nom, prix) VALUES (?, ?)');
                    $stmt->execute([$nom, $prix]);
                }
                $message = 'Boisson ajoutee.';
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare('DELETE FROM boissons WHERE id = ?')->execute([$id]);
                $message = 'Boisson supprimee.';
            }
        }
    }
}

$boissons = $pdo->query('SELECT * FROM boissons ORDER BY nom')->fetchAll(PDO::FETCH_ASSOC);
$categories = [
    'soft' => 'Softs', 'eau' => 'Eaux', 'jus' => 'Jus',
    'cocktail_sans_alcool' => 'Cocktails sans alcool', 'biere' => 'Bieres',
    'vin_rouge' => 'Vins rouges', 'vin_blanc' => 'Vins blancs',
    'vin_rose' => 'Vins roses', 'champagne' => 'Champagne', 'spiritueux' => 'Spiritueux',
];

include 'partials/layout.php';
?>

<div class="card-custom">
    <h1 class="h2 mb-4">Gestion des boissons</h1>

    <?php if ($message): ?><div class="alert alert-success" role="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add">
        <div class="col-md-4">
            <label class="form-label" for="boisson-nom">Nom</label>
            <input type="text" name="nom" id="boisson-nom" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="boisson-prix">Prix (EUR)</label>
            <input type="number" step="0.01" name="prix" id="boisson-prix" class="form-control" value="0">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="boisson-categorie">Categorie</label>
            <select name="categorie" id="boisson-categorie" class="form-select">
                <?php foreach ($categories as $k => $label): ?>
                <option value="<?= $k ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="boisson-description">Description</label>
            <input type="text" name="description" id="boisson-description" class="form-control">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <caption class="visually-hidden">Liste des boissons</caption>
            <thead><tr>
                <th scope="col">ID</th>
                <th scope="col">Nom</th>
                <th scope="col">Categorie</th>
                <th scope="col">Prix</th>
                <th scope="col"><span class="visually-hidden">Actions</span></th>
            </tr></thead>
            <tbody>
            <?php foreach ($boissons as $b): ?>
            <tr>
                <td><?= (int)$b['id'] ?></td>
                <td><?= htmlspecialchars($b['nom']) ?></td>
                <td><?= htmlspecialchars($categories[$b['categorie'] ?? ''] ?? ($b['categorie'] ?? '-')) ?></td>
                <td><?= number_format((float)$b['prix'], 2) ?> EUR</td>
                <td>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette boisson ?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
