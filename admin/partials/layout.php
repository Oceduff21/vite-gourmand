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
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<?= $extraHead ?? '' ?>

<style>

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

<div class="sidebar">

<h4><?= $isEmploye ? 'Espace Employe' : 'Administration' ?></h4>
<?php if ($isEmploye): ?>
<p class="small text-muted mb-3" style="margin-top:-20px">Bonjour, <?= htmlspecialchars($staffName) ?></p>
<?php endif; ?>

<a href="index.php">Dashboard</a>
<a href="admin-commandes.php" class="<?= strpos($_SERVER['PHP_SELF'] ?? '', 'commande') !== false ? 'active' : '' ?>">Commandes</a>
<a href="admin-menus.php">Menus</a>
<a href="admin-plats.php">Plats</a>
<a href="admin-boissons.php">Boissons</a>
<?php if ($isSuperAdmin): ?>
<a href="admin-users.php" class="<?= strpos($_SERVER['PHP_SELF'] ?? '', 'admin-users') !== false || strpos($_SERVER['PHP_SELF'] ?? '', 'edit-user') !== false ? 'active' : '' ?>">Utilisateurs</a>
<?php endif; ?>
<a href="admin-avis.php">Avis</a>

<hr>

<a href="../index.php">← Retour site</a>
<a href="../logout.php" class="text-danger">Deconnexion</a>

</div>

<div class="content">