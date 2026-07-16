<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$page = basename($_SERVER['PHP_SELF']);

function isActive($pages, $currentPage){
    return in_array($currentPage, (array)$pages) ? 'active-link' : '';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Vite & Gourmand</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Fonts + Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- CSS Perso -->
<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>
<a href="#main-content" class="visually-hidden-focusable btn btn-sm btn-primary position-absolute m-2">Aller au contenu</a>

<!-- NAVBAR MODERNE -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top custom-navbar">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <i class="fa-solid fa-utensils text-warning"></i>
            Vite & Gourmand
        </a>

        <!-- Mobile button -->
        <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="nav">

            <!-- Menu centre -->
            <ul class="navbar-nav mx-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link <?= isActive(['index.php'], $page) ?>" href="index.php">Accueil</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActive(['menus.php','menu.php'], $page) ?>" href="menus.php">Menus</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActive(['contact.php'], $page) ?>" href="contact.php">Contact</a>
                </li>

            </ul>

            <!-- Partie droite -->
            <ul class="navbar-nav align-items-lg-center">

                <?php if(isset($_SESSION["user_id"])): ?>

                    <li class="nav-item me-2">
                        <a class="nav-link" href="espace-utilisateur.php">
                            <i class="fa-regular fa-user me-1"></i> Mon espace
                        </a>
                    </li>

                    <?php if(in_array($_SESSION["user_role"] ?? '', ['admin', 'employe'])): ?>
                        <li class="nav-item me-2">
                            <a class="nav-link text-danger fw-semibold" href="admin/index.php">
                                <i class="fa-solid fa-shield-halved me-1"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="btn btn-outline-dark rounded-pill px-3" href="logout.php">
                            Déconnexion
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item me-2">
                        <a class="nav-link" href="register.php">Inscription</a>
                    </li>
                    <li class="nav-item"><a class="btn btn-dark rounded-pill px-4" href="login.php">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Connexion
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>
</nav>

<!-- ESPACE POUR NAVBAR FIXE -->
<div style="height:80px;"></div>

<!-- BREADCRUMB PRO -->
<?php
$current = 'Page';
$sub = null;

if($page === 'menu.php' && isset($_GET['id'])){
    try {
        require_once 'includes/db.php';

        $stmt = $pdo->prepare("SELECT titre FROM menus WHERE id=?");
        $stmt->execute([$_GET['id']]);
        $menu = $stmt->fetch();

        if($menu){
            $current = "Menus";
            $sub = $menu['titre'];
        }

    } catch(Exception $e){
        $current = "Menus";
    }

} else {
    $pages = [
        'menus.php' => 'Menus',
        'contact.php' => 'Contact',
        'commande.php' => 'Commande',
        'login.php' => 'Connexion',
        'espace-utilisateur.php' => 'Mon espace',
        'mentions-legales.php' => 'Mentions legales',
        'cgv.php' => 'CGV'
    ];

    $current = $pages[$page] ?? 'Page';
}
?>

<div class="breadcrumb-container py-2 bg-light border-bottom">
    <div class="container">
        <nav class="small">

            <a href="index.php" class="text-decoration-none text-muted">
                <i class="fa-solid fa-house"></i>
            </a>

            <?php if($page !== 'index.php'): ?>

                <span class="mx-2 text-muted"></span>

                <?php if(isset($sub)): ?>

                    <a href="menus.php" class="text-decoration-none text-muted">
                        <?= $current ?>
                    </a>

                    <span class="mx-2 text-muted"></span>

                    <span class="fw-semibold"><?= htmlspecialchars($sub) ?></span>

                <?php else: ?>

                    <span class="fw-semibold"><?= $current ?></span>

                <?php endif; ?>

            <?php endif; ?>

        </nav>
    </div>
</div>

<!-- CONTENU -->
<?php $mainClass = ($page === 'index.php') ? '' : ' class="container mt-4"'; ?>
<main id="main-content"<?= $mainClass ?>>