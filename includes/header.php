<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/security.php';

sendSecurityHeaders();

$page = basename($_SERVER['PHP_SELF']);
$seoDefaults = getDefaultSeo();
$pageTitle = $pageTitle ?? $seoDefaults['title'];
$pageDescription = $pageDescription ?? $seoDefaults['description'];
$pageKeywords = $pageKeywords ?? $seoDefaults['keywords'];
$pageOgImage = $pageOgImage ?? $seoDefaults['og_image'];
$pageOgType = $pageOgType ?? $seoDefaults['type'];
$pageCanonical = $pageCanonical ?? buildCanonicalUrl();
$pageNoIndex = $pageNoIndex ?? false;
$ogImageUrl = str_starts_with($pageOgImage, 'http') ? $pageOgImage : buildCanonicalUrl('/' . ltrim($pageOgImage, '/'));

function isActive($pages, $currentPage){
    return in_array($currentPage, (array)$pages) ? 'active-link' : '';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
<link rel="canonical" href="<?= htmlspecialchars($pageCanonical) ?>">
<?php if ($pageNoIndex): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>

<meta property="og:type" content="<?= htmlspecialchars($pageOgType) ?>">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta property="og:url" content="<?= htmlspecialchars($pageCanonical) ?>">
<meta property="og:image" content="<?= htmlspecialchars($ogImageUrl) ?>">
<meta property="og:locale" content="fr_FR">
<meta name="twitter:card" content="summary_large_image">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Fonts + Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- CSS Perso -->
<link rel="stylesheet" href="assets/css/style.css">
<link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FoodEstablishment",
  "name": "Vite & Gourmand",
  "description": <?= json_encode($pageDescription, JSON_UNESCAPED_UNICODE) ?>,
  "url": <?= json_encode(rtrim(getSiteBaseUrl(), '/')) ?>,
  "telephone": "+33412345678",
  "email": "contact@vite-gourmand.fr",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "11 Rue Verteuil",
    "addressLocality": "Bordeaux",
    "postalCode": "33000",
    "addressCountry": "FR"
  },
  "servesCuisine": "French",
  "priceRange": "€€€"
}
</script>

</head>

<body>
<a href="#main-content" class="skip-link visually-hidden-focusable">Aller au contenu principal</a>

<!-- NAVBAR MODERNE -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top custom-navbar" aria-label="Navigation principale">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
            <i class="fa-solid fa-utensils text-warning" aria-hidden="true"></i>
            Vite & Gourmand
        </a>

        <!-- Mobile button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
            aria-controls="nav" aria-expanded="false" aria-label="Ouvrir ou fermer le menu">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
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
                    <a class="nav-link <?= isActive(['a-propos.php'], $page) ?>" href="a-propos.php">A propos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActive(['avis.php'], $page) ?>" href="avis.php">Avis</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActive(['faq.php'], $page) ?>" href="faq.php">FAQ</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActive(['contact.php'], $page) ?>" href="contact.php">Contact</a>
                </li>

            </ul>

            <!-- Partie droite -->
            <ul class="navbar-nav align-items-lg-center">

                <?php if(isset($_SESSION["user_id"])):
                    $headerNotifCount = 0;
                    try {
                        require_once __DIR__ . '/db.php';
                        require_once __DIR__ . '/user-helpers.php';
                        $headerNotifCount = countUnreadNotifications($pdo, (int)$_SESSION['user_id']);
                    } catch (Throwable $e) {
                        $headerNotifCount = 0;
                    }
                ?>

                    <li class="nav-item me-2">
                        <a class="nav-link position-relative" href="espace-utilisateur.php">
                            <i class="fa-regular fa-user me-1" aria-hidden="true"></i> Mon espace
                            <?php if ($headerNotifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" aria-label="<?= (int)$headerNotifCount ?> notification(s) non lue(s)"><?= $headerNotifCount ?></span>
                            <?php endif; ?>
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
        'a-propos.php' => 'A propos',
        'faq.php' => 'FAQ',
        'avis.php' => 'Avis',
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
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item">
                    <a href="index.php"><span class="visually-hidden">Accueil</span><i class="fa-solid fa-house" aria-hidden="true"></i></a>
                </li>
            <?php if($page !== 'index.php'): ?>
                <?php if(isset($sub)): ?>
                <li class="breadcrumb-item"><a href="menus.php"><?= htmlspecialchars($current) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($sub) ?></li>
                <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($current) ?></li>
                <?php endif; ?>
            <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- CONTENU -->
<?php $mainClass = ($page === 'index.php') ? '' : ' class="container mt-4"'; ?>
<main id="main-content"<?= $mainClass ?>>