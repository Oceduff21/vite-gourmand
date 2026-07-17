<?php
require_once __DIR__ . '/auth.php';
requireAdminAccess();

$pageTitle = $pageTitle ?? 'Admin';
$extraHead = $extraHead ?? '';
$isSuperAdmin = isSuperAdmin();
$isEmploye = isEmploye();
$canViewFinancials = canViewAdminFinancials();
$staffName = getStaffDisplayName();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" href="../assets/images/favicon.svg" type="image/svg+xml">
<?= $extraHead ?? '' ?>

<style>
.skip-link{position:absolute;top:0;left:240px;z-index:10001;padding:.5rem 1rem;background:#1a1a2e;color:#fff;text-decoration:none;border-radius:0 0 8px 8px}
.skip-link:focus-visible{outline:3px solid #c9a227;outline-offset:2px}
.sidebar a:focus-visible,.content a:focus-visible,.content button:focus-visible,.content input:focus-visible,.content select:focus-visible,.content textarea:focus-visible{outline:3px solid #6366f1;outline-offset:2px}

body{
    background:#f5f7fb;
    font-family:'Segoe UI', sans-serif;
    overflow-x:hidden;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:0;
    left:0;
    height:100vh;
    width:220px;
    background:white;
    border-right:1px solid #e5e7eb;
    padding:20px;
    overflow-y:auto;
    z-index:1000;
}

.sidebar h4{
    margin-bottom:30px;
}

.sidebar a{
    display:block;
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    color:#374151;
    text-decoration:none;
}

.sidebar a.active,
.sidebar a:hover{
    background:#6366f1;
    color:white;
}

/* CONTENT */
.content{
    margin-left:240px;
    padding:30px;
    min-height:100vh;
    max-width:calc(100vw - 240px);
}

/* CARD */
.card-custom{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

/* CHARTS — hauteur fixe pour eviter expansion infinie au scroll */
.chart-wrap{
    position:relative;
    height:260px;
    max-height:260px;
    overflow:hidden;
}
.chart-wrap-sm{ height:200px; max-height:200px; }
.chart-wrap-lg{ height:300px; max-height:300px; }
.chart-wrap canvas{
    display:block;
    width:100%!important;
    max-height:100%!important;
}

.plat-allergenes{display:flex;flex-wrap:wrap;gap:4px}
.allergen-badge{background:#fef3c7;color:#92400e;font-size:.68rem;font-weight:600;padding:2px 6px}
.allergen-none{background:#f1f5f9;color:#64748b;font-size:.68rem;font-weight:500;padding:2px 6px}

@media(max-width:991px){
    .sidebar{
        position:relative;
        width:100%;
        height:auto;
        border-right:none;
        border-bottom:1px solid #e5e7eb;
    }
    .content{
        margin-left:0;
        max-width:100%;
        padding:20px 16px;
    }
}

</style>

</head>

<body>

<a href="#admin-main" class="skip-link visually-hidden-focusable">Aller au contenu principal</a>

<nav class="sidebar" aria-label="Navigation administration">

<h4><?= $isEmploye ? 'Espace Employe' : 'Administration' ?></h4>
<?php if ($isEmploye): ?>
<p class="small text-muted mb-3" style="margin-top:-20px">Bonjour, <?= htmlspecialchars($staffName) ?></p>
<?php endif; ?>

<?php
$adminSelf = basename($_SERVER['PHP_SELF'] ?? '');
$adminNavActive = static function (array $needles) use ($adminSelf): bool {
    foreach ($needles as $needle) {
        if (str_contains($adminSelf, $needle)) {
            return true;
        }
    }
    return false;
};
$adminNavCurrent = static function (bool $active): string {
    return $active ? ' aria-current="page"' : '';
};
?>
<a href="index.php" class="<?= $adminSelf === 'index.php' ? 'active' : '' ?>"<?= $adminNavCurrent($adminSelf === 'index.php') ?>>Dashboard</a>
<a href="admin-commandes.php" class="<?= $adminNavActive(['commande']) ? 'active' : '' ?>"<?= $adminNavCurrent($adminNavActive(['commande'])) ?>>Commandes</a>
<a href="admin-menus.php" class="<?= $adminNavActive(['admin-menus', 'add-menu', 'edit-menu']) ? 'active' : '' ?>"<?= $adminNavCurrent($adminNavActive(['admin-menus', 'add-menu', 'edit-menu'])) ?>>Menus</a>
<a href="admin-plats.php" class="<?= $adminNavActive(['admin-plats', 'edit-plat']) ? 'active' : '' ?>"<?= $adminNavCurrent($adminNavActive(['admin-plats', 'edit-plat'])) ?>>Plats</a>
<a href="admin-boissons.php" class="<?= $adminSelf === 'admin-boissons.php' ? 'active' : '' ?>"<?= $adminNavCurrent($adminSelf === 'admin-boissons.php') ?>>Boissons</a>
<a href="admin-horaires.php" class="<?= $adminSelf === 'admin-horaires.php' ? 'active' : '' ?>"<?= $adminNavCurrent($adminSelf === 'admin-horaires.php') ?>>Horaires</a>
<?php if ($isSuperAdmin): ?>
<a href="admin-users.php" class="<?= $adminNavActive(['admin-users', 'edit-user']) ? 'active' : '' ?>"<?= $adminNavCurrent($adminNavActive(['admin-users', 'edit-user'])) ?>>Utilisateurs</a>
<?php endif; ?>
<a href="admin-avis.php" class="<?= $adminSelf === 'admin-avis.php' ? 'active' : '' ?>"<?= $adminNavCurrent($adminSelf === 'admin-avis.php') ?>>Avis</a>

<hr>

<a href="../index.php">← Retour site</a>
<a href="../logout.php" class="text-danger">Deconnexion</a>

</nav>

<main id="admin-main" class="content">