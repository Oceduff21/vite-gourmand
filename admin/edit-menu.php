<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';
require '../includes/menu-helpers.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die('ID manquant');
}

$stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
$stmt->execute([$id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$menu) {
    die('Menu introuvable');
}

$entrees = $pdo->query("SELECT * FROM plats WHERE type='entree' ORDER BY nom")->fetchAll();
$plats = $pdo->query("SELECT * FROM plats WHERE type='plat' ORDER BY nom")->fetchAll();
$desserts = $pdo->query("SELECT * FROM plats WHERE type='dessert' ORDER BY nom")->fetchAll();
$boissons = $pdo->query('SELECT * FROM boissons ORDER BY nom')->fetchAll();

$selected = getMenuOptionIds($pdo, $id);
$mb = $pdo->prepare('SELECT boisson_id FROM menu_boissons WHERE menu_id = ?');
$mb->execute([$id]);
$selectedBoissons = array_column($mb->fetchAll(), 'boisson_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token CSRF invalide.');
    }
    $errors = [];
    if (count($_POST['entrees'] ?? []) !== MENU_OPTIONS_REQUIRED) {
        $errors[] = '3 entrees obligatoires';
    }
    if (count($_POST['plats'] ?? []) !== MENU_OPTIONS_REQUIRED) {
        $errors[] = '3 plats obligatoires';
    }
    if (count($_POST['desserts'] ?? []) !== MENU_OPTIONS_REQUIRED) {
        $errors[] = '3 desserts obligatoires';
    }

    if ($errors) {
        $errorMsg = implode(', ', $errors);
    } else {
        $pdo->beginTransaction();
        $fields = [
            'titre' => trim($_POST['titre']),
            'description' => trim($_POST['description'] ?? ''),
            'theme' => trim($_POST['theme'] ?? ''),
            'regime' => $_POST['regime'] ?? 'classique',
            'prix' => (float)$_POST['prix'],
            'min_personnes' => (int)$_POST['min'],
            'stock' => (int)($_POST['stock'] ?? 0),
            'conditions' => trim($_POST['conditions'] ?? ''),
            'delai_jours' => (int)($_POST['delai_jours'] ?? 7),
        ];
        $img = normalizeMenuImageFilename(trim($_POST['image_principale'] ?? ''));
        updateMenuWithImage($pdo, $id, $fields, $img ?? '');

        $pdo->prepare('DELETE FROM menu_options WHERE menu_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM menu_boissons WHERE menu_id = ?')->execute([$id]);

        foreach ($_POST['entrees'] as $e) {
            $pdo->prepare("INSERT INTO menu_options (menu_id, plat_id, type) VALUES (?, ?, 'entree')")->execute([$id, (int)$e]);
        }
        foreach ($_POST['plats'] as $p) {
            $pdo->prepare("INSERT INTO menu_options (menu_id, plat_id, type) VALUES (?, ?, 'plat')")->execute([$id, (int)$p]);
        }
        foreach ($_POST['desserts'] as $d) {
            $pdo->prepare("INSERT INTO menu_options (menu_id, plat_id, type) VALUES (?, ?, 'dessert')")->execute([$id, (int)$d]);
        }
        foreach ($_POST['boissons'] ?? [] as $b) {
            $pdo->prepare('INSERT INTO menu_boissons (menu_id, boisson_id) VALUES (?, ?)')->execute([$id, (int)$b]);
        }

        $pdo->commit();
        header('Location: admin-menus.php');
        exit();
    }
}

require __DIR__ . '/partials/layout.php';
?>

<h1 class="h2 mb-4">Modifier le menu</h1>

<?php if (!empty($errorMsg)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form method="POST" id="menu-admin-form">
<?= csrfField() ?>
<?php require __DIR__ . '/partials/menu-form.php'; ?>
<button type="submit" class="btn btn-primary btn-lg">Enregistrer</button>
<a href="admin-menus.php" class="btn btn-outline-secondary">Annuler</a>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>
