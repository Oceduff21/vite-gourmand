<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$page = basename($_SERVER['PHP_SELF']);

$breadcrumbs = [
    'index.php' => 'Accueil',
    'menus.php' => 'Menus',
    'menu.php' => 'Détail du menu',
    'contact.php' => 'Contact',
    'commande.php' => 'Commande',
    'login.php' => 'Connexion',
    'espace-utilisateur.php' => 'Mon espace'
];

$current = $breadcrumbs[$page] ?? 'Page';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Vite & Gourmand</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light custom-navbar">

<div class="container">

<a class="navbar-brand fw-bold" href="index.php">
Vite & Gourmand
</a>

<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="nav">

<ul class="navbar-nav mx-auto">

<li class="nav-item">
<a class="nav-link <?= ($page == 'index.php') ? 'active-link' : '' ?>" href="index.php">
Accueil
</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($page == 'menus.php' || $page == 'menu.php') ? 'active-link' : '' ?>" href="menus.php">
Menus
</a>
</li>

<li class="nav-item">
<a class="nav-link <?= ($page == 'contact.php') ? 'active-link' : '' ?>" href="contact.php">
Contact
</a>
</li>

</ul>

<ul class="navbar-nav">

<?php if(isset($_SESSION["user_id"])): ?>

<li class="nav-item">
<a class="nav-link" href="espace-utilisateur.php">Mon espace</a>
</li>

<?php if($_SESSION["user_role"] === "admin"): ?>
<li class="nav-item">
<a class="nav-link admin-link" href="admin.php">Admin</a>
</li>
<?php endif; ?>

<li class="nav-item">
<a class="btn btn-outline-dark ms-2" href="logout.php">Déconnexion</a>
</li>

<?php else: ?>

<li class="nav-item">
<a class="btn btn-dark" href="login.php">Connexion</a>
</li>

<?php endif; ?>

</ul>

</div>

</div>

</nav>

<!-- BREADCRUMB (SOUS HEADER) -->
<?php
$page = basename($_SERVER['PHP_SELF']);

$current = 'Page';
$sub = null;

/* PAGE MENU DETAIL */
if($page === 'menu.php' && isset($_GET['id'])){
    
    try {
        require_once 'includes/db.php';
        require_once 'includes/helpers.php';

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

}
/* AUTRES PAGES */
else{

    $pages = [
        'menus.php' => 'Menus',
        'contact.php' => 'Contact',
        'commande.php' => 'Commande',
        'login.php' => 'Connexion',
        'espace-utilisateur.php' => 'Mon espace'
    ];

    $current = $pages[$page] ?? 'Page';
}
?>

<div class="breadcrumb-container">
    <div class="container">
        <nav class="breadcrumb-custom">

            <a href="index.php">Accueil</a>

            <?php if($page !== 'index.php'): ?>

                <span>›</span>

                <?php if(isset($sub)): ?>
                    <a href="menus.php"><?= $current ?></a>
                    <span>›</span>
                    <span class="active"><?= htmlspecialchars($sub) ?></span>
                <?php else: ?>
                    <span class="active"><?= $current ?></span>
                <?php endif; ?>

            <?php endif; ?>

        </nav>
    </div>
</div>

<!-- CONTENU -->
<div class="container mt-4">