<?php
require __DIR__ . '/partials/auth.php';
requireAdminAccess();

require '../includes/db.php';
require '../includes/menu-helpers.php';

$entrees = $pdo->query("SELECT * FROM plats WHERE type='entree' ORDER BY nom")->fetchAll();
$plats = $pdo->query("SELECT * FROM plats WHERE type='plat' ORDER BY nom")->fetchAll();
$desserts = $pdo->query("SELECT * FROM plats WHERE type='dessert' ORDER BY nom")->fetchAll();
$boissons = $pdo->query('SELECT * FROM boissons ORDER BY nom')->fetchAll();
$menu = [];
$selected = ['entree' => [], 'plat' => [], 'dessert' => []];
$selectedBoissons = [];

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
        $stmt = $pdo->prepare('
            INSERT INTO menus (titre, description, theme, regime, prix, min_personnes, stock, conditions, delai_jours)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            trim($_POST['titre']),
            trim($_POST['description'] ?? ''),
            trim($_POST['theme'] ?? ''),
            $_POST['regime'] ?? 'classique',
            (float)$_POST['prix'],
            (int)$_POST['min'],
            (int)($_POST['stock'] ?? 0),
            trim($_POST['conditions'] ?? ''),
            (int)($_POST['delai_jours'] ?? 7),
        ]);
        $menu_id = (int)$pdo->lastInsertId();

        foreach ($_POST['entrees'] as $e) {
            $pdo->prepare("INSERT INTO menu_options (menu_id, plat_id, type) VALUES (?, ?, 'entree')")->execute([$menu_id, (int)$e]);
        }
        foreach ($_POST['plats'] as $p) {
            $pdo->prepare("INSERT INTO menu_options (menu_id, plat_id, type) VALUES (?, ?, 'plat')")->execute([$menu_id, (int)$p]);
        }
        foreach ($_POST['desserts'] as $d) {
            $pdo->prepare("INSERT INTO menu_options (menu_id, plat_id, type) VALUES (?, ?, 'dessert')")->execute([$menu_id, (int)$d]);
        }
        foreach ($_POST['boissons'] ?? [] as $b) {
            $pdo->prepare('INSERT INTO menu_boissons (menu_id, boisson_id) VALUES (?, ?)')->execute([$menu_id, (int)$b]);
        }

        $pdo->commit();
        header('Location: admin-menus.php');
        exit();
    }
}

require __DIR__ . '/partials/layout.php';
?>

<h2 class="mb-4">Creer un menu</h2>

<?php if (!empty($errorMsg)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form method="POST" id="menu-admin-form">
<?= csrfField() ?>
<?php require __DIR__ . '/partials/menu-form.php'; ?>
<button type="submit" class="btn btn-success btn-lg">Creer le menu</button>
<a href="admin-menus.php" class="btn btn-outline-secondary">Annuler</a>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>
